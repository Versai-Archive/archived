<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\scheduler;

use function is_scalar;
use function serialize;
use function spl_object_id;
use function unserialize;

/**
 * Class used to run async tasks in other threads.
 *
 * An AsyncTask does not have its own thread. It is queued into an AsyncPool and executed if there is an async worker
 * with no AsyncTask running. Therefore, an AsyncTask SHOULD NOT execute for more than a few seconds. For tasks that
 * run for a long time or infinitely, start another {@link \pocketmine\Thread} instead.
 *
 * WARNING: Any non-Threaded objects WILL BE SERIALIZED when assigned to members of AsyncTasks or other Threaded object.
 * If later accessed from said Threaded object, you will be operating on a COPY OF THE OBJECT, NOT THE ORIGINAL OBJECT.
 * If you want to store non-serializable objects to access when the task completes, store them using
 * {@link AsyncTask#storeLocal}.
 *
 * WARNING: As of pthreads v3.1.6, arrays are converted to Volatile objects when assigned as members of Threaded objects.
 * Keep this in mind when using arrays stored as members of your AsyncTask.
 *
 * WARNING: Do not call PocketMine-MP API methods from other Threads!!
 */
abstract class AsyncTask extends \Threaded{
	/**
	 * @var \ArrayObject|mixed[] object hash => mixed data
	 *
	 * Used to store objects which are only needed on one thread and should not be serialized.
	 */
	private static $threadLocalStorage = null;

	/** @var AsyncWorker $worker */
	public $worker = null;

	/** @var \Threaded */
	public $progressUpdates;

	private $result = null;
	private $serialized = false;
	private $cancelRun = false;
	/** @var bool */
	private $submitted = false;

	private $crashed = false;
	/** @var bool */
	private $finished = false;

	public function run() : void{
		$this->result = null;

		if(!$this->cancelRun){
			try{
				$this->onRun();
			}catch(\Throwable $e){
				$this->crashed = true;
				$this->worker->handleException($e);
			}
		}

		$this->finished = true;
	}

	public function isCrashed() : bool{
		return $this->crashed or $this->isTerminated();
	}

	/**
	 * Returns whether this task has finished executing, whether successfully or not. This differs from isRunning()
	 * because it is not true prior to task execution.
	 *
	 * @return bool
	 */
	public function isFinished() : bool{
		return $this->finished or $this->isCrashed();
	}

	/**
	 * @return mixed
	 */
	public function getResult(){
		return $this->serialized ? unserialize($this->result) : $this->result;
	}

	public function cancelRun() : void{
		$this->cancelRun = true;
	}

	public function hasCancelledRun() : bool{
		return $this->cancelRun;
	}

	/**
	 * @return bool
	 */
	public function hasResult() : bool{
		return $this->result !== null;
	}

	/**
	 * @param mixed $result
	 */
	public function setResult($result) : void{
		$this->result = ($this->serialized = !is_scalar($result)) ? serialize($result) : $result;
	}

	public function setSubmitted() : void{
		$this->submitted = true;
	}

	/**
	 * @return bool
	 */
	public function isSubmitted() : bool{
		return $this->submitted;
	}

	/**
	 * @see AsyncWorker::getFromThreadStore()
	 *
	 * @param string $identifier
	 *
	 * @return mixed
	 */
	public function getFromThreadStore(string $identifier){
		if($this->worker === null or $this->isFinished()){
			throw new \BadMethodCallException("Objects stored in AsyncWorker thread-local storage can only be retrieved during task execution");
		}
		return $this->worker->getFromThreadStore($identifier);
	}

	/**
	 * @see AsyncWorker::saveToThreadStore()
	 *
	 * @param string $identifier
	 * @param mixed  $value
	 */
	public function saveToThreadStore(string $identifier, $value) : void{
		if($this->worker === null or $this->isFinished()){
			throw new \BadMethodCallException("Objects can only be added to AsyncWorker thread-local storage during task execution");
		}
		$this->worker->saveToThreadStore($identifier, $value);
	}

	/**
	 * @see AsyncWorker::removeFromThreadStore()
	 *
	 * @param string $identifier
	 */
	public function removeFromThreadStore(string $identifier) : void{
		if($this->worker === null or $this->isFinished()){
			throw new \BadMethodCallException("Objects can only be removed from AsyncWorker thread-local storage during task execution");
		}
		$this->worker->removeFromThreadStore($identifier);
	}

	/**
	 * Actions to execute when run
	 */
	abstract public function onRun() : void;

	/**
	 * Actions to execute when completed (on main thread)
	 * Implement this if you want to handle the data in your AsyncTask after it has been processed
	 */
	public function onCompletion() : void{

	}

	/**
	 * Call this method from {@link AsyncTask#onRun} (AsyncTask execution thread) to schedule a call to
	 * {@link AsyncTask#onProgressUpdate} from the main thread with the given progress parameter.
	 *
	 * @param mixed $progress A value that can be safely serialize()'ed.
	 */
	public function publishProgress($progress) : void{
		$this->progressUpdates[] = serialize($progress);
	}

	/**
	 * @internal Only call from AsyncPool.php on the main thread
	 */
	public function checkProgressUpdates() : void{
		while($this->progressUpdates->count() !== 0){
			$progress = $this->progressUpdates->shift();
			$this->onProgressUpdate(unserialize($progress));
		}
	}

	/**
	 * Called from the main thread after {@link AsyncTask#publishProgress} is called.
	 * All {@link AsyncTask#publishProgress} calls should result in {@link AsyncTask#onProgressUpdate} calls before
	 * {@link AsyncTask#onCompletion} is called.
	 *
	 * @param mixed $progress The parameter passed to {@link AsyncTask#publishProgress}. It is serialize()'ed
	 *                         and then unserialize()'ed, as if it has been cloned.
	 */
	public function onProgressUpdate($progress) : void{

	}

	/**
	 * Saves mixed data in thread-local storage. Data stored using this storage is **only accessible from the thread it
	 * was stored on**. Data stored using this method will **not** be serialized.
	 * This can be used to store references to variables which you need later on on the same thread, but not others.
	 *
	 * For example, plugin references could be stored in the constructor of the async task (which is called on the main
	 * thread) using this, and then fetched in onCompletion() (which is also called on the main thread), without them
	 * becoming serialized.
	 *
	 * Scalar types can be stored directly in class properties instead of using this storage.
	 *
	 * Objects stored in this storage can be retrieved using fetchLocal() on the same thread that this method was called
	 * from.
	 *
	 * WARNING: Use this method carefully. It might take a long time before an AsyncTask is completed. The thread this
	 * is called on will keep a strong reference to variables stored using method. This may result in a light memory
	 * leak. Usually this does not cause memory failure, but be aware that the object may be no longer usable when the
	 * AsyncTask completes. Since a strong reference is retained, the objects still exist, but the implementation is
	 * responsible for checking whether these objects are still usable.
	 * (E.g. a {@link \pocketmine\Level} object is no longer usable because it is unloaded while the AsyncTask is
	 * executing, or even a plugin might be unloaded).
	 *
	 * @param mixed $complexData the data to store
	 */
	protected function storeLocal($complexData) : void{
		if(self::$threadLocalStorage === null){
			/*
			 * It's necessary to use an object (not array) here because pthreads is stupid. Non-default array statics
			 * will be inherited when task classes are copied to the worker thread, which would cause unwanted
			 * inheritance of primitive thread-locals, which we really don't want for various reasons.
			 * It won't try to inherit objects though, so this is the easiest solution.
			 */
			self::$threadLocalStorage = new \ArrayObject();
		}
		self::$threadLocalStorage[spl_object_id($this)] = $complexData;
	}

	/**
	 * Retrieves data stored in thread-local storage.
	 *
	 * If you used storeLocal(), you can use this on the same thread to fetch data stored. This should be used during
	 * onProgressUpdate() and onCompletion() to fetch thread-local data stored on the parent thread.
	 *
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException if no data were stored by this AsyncTask instance.
	 */
	protected function fetchLocal(){
		if(self::$threadLocalStorage === null or !isset(self::$threadLocalStorage[spl_object_id($this)])){
			throw new \InvalidArgumentException("No matching thread-local data found on this thread");
		}

		return self::$threadLocalStorage[spl_object_id($this)];
	}

	final public function __destruct(){
		$this->reallyDestruct();
		if(self::$threadLocalStorage !== null and isset(self::$threadLocalStorage[$h = spl_object_id($this)])){
			unset(self::$threadLocalStorage[$h]);
			if(self::$threadLocalStorage->count() === 0){
				self::$threadLocalStorage = null;
			}
		}
	}

	/**
	 * Override this to do normal __destruct() cleanup from a child class.
	 */
	protected function reallyDestruct() : void{

	}
}
