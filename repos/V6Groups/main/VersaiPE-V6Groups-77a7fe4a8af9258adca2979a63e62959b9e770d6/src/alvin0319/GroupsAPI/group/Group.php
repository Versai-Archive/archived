<?php

/**
 * ____                              _    ____ ___
 * / ___|_ __ ___  _   _ _ __  ___   / \  |  _ \_ _|
 * | |  _| '__/ _ \| | | | '_ \/ __| / _ \ | |_) | |
 * | |_| | | | (_) | |_| | |_) \__ \/ ___ \|  __/| |
 * \____|_|  \___/ \__,_| .__/|___/_/   \_\_|  |___|
 * |_|
 *
 * MIT License
 *
 * Copyright (c) 2022 alvin0319
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @noinspection PhpUnusedParameterInspection
 */

declare(strict_types=1);

namespace alvin0319\GroupsAPI\group;

use alvin0319\GroupsAPI\util\GroupPriority;
use function array_replace_recursive;
use function array_search;
use function array_values;
use function in_array;

final class Group{

	protected string $name;

	protected array $permissions = [], $extraPermissions = [], $combinedPermissions = [];
	protected array $parents = [], $children = [];

	protected int $priority = GroupPriority::PRIORITY_MEMBER;

	public function __construct(string $name, array $permissions, int $priority){
		$this->name = $name;
		$this->permissions = $permissions;
		$this->priority = $priority;
        $this->updateChildrenPermissions();
        $this->recalculateCombinedPermissions();
	}

	public function getName() : string{ return $this->name; }

	public function getPermissions() : array{ return $this->permissions; }

	public function getPriority() : int{ return $this->priority; }

	public function addPermission(string $permission) : void{
	    $this->permissions[] = $permission;
	    $this->updateChildrenPermissions();
	    $this->recalculateCombinedPermissions();
	}

	public function hasPermission(string $permission) : ?bool{ return in_array($permission, $this->permissions, true); }

	public function setPermissions(array $permissions) : void{
		$this->permissions = $permissions;
        $this->updateChildrenPermissions();
        $this->recalculateCombinedPermissions();
	}

	public function removePermission(string $permission) : void{
		if(($key = array_search($permission, $this->permissions, true)) !== false){
			unset($this->permissions[$key]);
			$this->permissions = array_values($this->permissions);
            $this->updateChildrenPermissions();
            $this->recalculateCombinedPermissions();
		}
	}

	public function setPriority(int $priority) : void{
		$this->priority = $priority;
	}

	public function inheritGroup(Group $group) : void{
	    $this->parents[$group->getName()] = $group;
	    $group->addChild($this);
        $this->refreshInheritedPermissions();
        $this->recalculateCombinedPermissions();
    }

    public function unInheritGroup(Group $group) : void{
	    unset($this->parents[$group->getName()]);
	    $group->removeChild($this);
        $this->refreshInheritedPermissions();
        $this->recalculateCombinedPermissions();
    }

    public function addChild(Group $child):void { $this->children[$child->getName()] = $child; }

    public function removeChild(Group $child):void {
        unset($this->children[$child->getName()]);
    }

    public function getChildren() : array{ return $this->children; }

    public function getParents() : array{ return $this->parents; }

    public function updateChildrenPermissions(): void {
        foreach($this->children as $role) {
            $role->refreshInheritedPermissions();
        }
    }

    public function refreshInheritedPermissions():void {
        $this->extraPermissions = [];
        foreach($this->parents as $group){
            foreach($group->getPermissions() as $permission){
                $this->extraPermissions[] = $permission;
            }
        }
    }

    public function getExtraPermissions(): array {
        return $this->extraPermissions;
    }

    private function recalculateCombinedPermissions():void {
        $this->combinedPermissions = array_replace_recursive($this->getExtraPermissions(), $this->getPermissions());
    }

    public function getCombinedPermissions(): array {
        return $this->combinedPermissions;
    }
}