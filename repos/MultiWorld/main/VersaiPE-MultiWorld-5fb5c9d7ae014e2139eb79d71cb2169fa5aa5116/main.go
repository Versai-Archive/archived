package MultiWorld

import (
	"fmt"
	"github.com/df-mc/dragonfly/server"
	"github.com/df-mc/dragonfly/server/world"
	"github.com/df-mc/dragonfly/server/world/mcdb"
	"github.com/df-mc/goleveldb/leveldb/opt"
	"github.com/sirupsen/logrus"
	"sync"
)

const worldsDir = "worlds/"

type WorldManager struct {
	srv *server.Server

	log *logrus.Logger

	worldsMu  sync.RWMutex
	allWorlds []string
	worlds    map[string]*world.World
}

func (manager *WorldManager) LoadWorld(worldName string) error {
	if _, ok := manager.GetWorld(worldName); ok {
		return fmt.Errorf("world is already loaded")
	}

	manager.log.Debugf("Loading world...")
	p, err := mcdb.New(manager.log, worldsDir+worldName, opt.DefaultCompression)
	if err != nil {
		return fmt.Errorf("error loading world: %v", err)
	}

	p.SaveSettings(&world.Settings{
		Name: worldName,
		Spawn: cube.Pos{0, -55, 0},
	})

	w := world.Config{
		Dim:      world.Overworld,
		Log:      manager.log,
		ReadOnly: true,
		Provider: p,
	}.New()

	w.SetTickRange(0)
	w.SetTime(6000)
	w.StopTime()

	w.StopWeatherCycle()
	w.SetDefaultGameMode(world.GameModeSurvival)

	manager.worldsMu.Lock()
	manager.worlds[worldName] = w
	manager.worldsMu.Unlock()

	manager.log.Debugf(`Loaded world "%v".`, w.Name())
	return nil
}

func (manager *WorldManager) UnloadWorld(w *world.World) error {
	if w == manager.srv.World() {
		return fmt.Errorf("the default world cannot be unloaded")
	}

	if _, ok := manager.GetWorld(w.Name()); !ok {
		return fmt.Errorf("world isn't loaded")
	}

	manager.log.Debugf("Unloading world '%v'\n", w.Name())
	for _, p := range manager.srv.Players() {
		if p.World() == w {
			// Teleport all entities from the world, to the default world
			manager.srv.World().AddEntity(p)
			// Teleport them to the spawn of the world
			p.Teleport(manager.srv.World().Spawn().Vec3Middle())
		}
	}

	manager.worldsMu.Lock()
	delete(manager.worlds, w.Name())
	manager.worldsMu.Unlock()

	if err := w.Close(); err != nil {
		return fmt.Errorf("error closing world: %v", err)
	}
	manager.log.Debugf("Unloaded world '%v'\n", w.Name())
	return nil
}

func (manager *WorldManager) GetWorld(name string) (*world.World, bool) {
	manager.worldsMu.RLock()
	w, ok := manager.worlds[name]
	manager.worldsMu.RUnlock()
	return w, ok
}

// TODO
//func DeleteWorld() {
//
//}
