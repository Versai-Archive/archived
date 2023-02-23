package multiworld

import (
	"fmt"
	"github.com/df-mc/dragonfly/server"
	"github.com/df-mc/dragonfly/server/block"
	"github.com/df-mc/dragonfly/server/block/cube"
	"github.com/df-mc/dragonfly/server/world"
	"github.com/df-mc/dragonfly/server/world/mcdb"
	"github.com/df-mc/goleveldb/leveldb/opt"
	"github.com/go-gl/mathgl/mgl64"
	"github.com/sirupsen/logrus"
	"os"
	"sync"
)

type Manager struct {
	srv *server.Server

	path string

	log *logrus.Logger

	worldsMu  sync.RWMutex
	allWorlds []string
	worlds    map[string]*world.World
}

func New(server *server.Server, path string, log *logrus.Logger) *Manager {
	_ = os.Mkdir(path, 0644)
	dir, err := os.ReadDir(path)
	if err != nil {
		return nil
	}
	var worlds = []string{}
	for _, f := range dir {
		worlds = append(worlds, f.Name())
	}
	defwrld := server.World()
	return &Manager{
		srv:       server,
		path:      path,
		log:       log,
		allWorlds: worlds,
		worlds: map[string]*world.World{
			defwrld.Name(): defwrld,
		},
	}
}

func (m *Manager) AllWorlds() []string {
	return m.allWorlds
}

func (m *Manager) DefaultWorld() *world.World {
	return m.srv.World()
}

func (m *Manager) Worlds() []*world.World {
	m.worldsMu.RLock()
	worlds := make([]*world.World, 0, len(m.worlds))
	for _, w := range m.worlds {
		worlds = append(worlds, w)
	}
	m.worldsMu.RUnlock()
	return worlds
}

func (m *Manager) World(name string) (*world.World, bool) {
	m.worldsMu.RLock()
	w, ok := m.worlds[name]
	m.worldsMu.RUnlock()
	return w, ok
}

func (m *Manager) LoadWorld(path, worldName string) error {
	if _, ok := m.World(worldName); ok {
		return fmt.Errorf("world is already loaded")
	}

	log := m.log.WithField("dimension", "overworld")
	log.Debugf("Loading world...")
	p, err := mcdb.New(m.log, m.path+"/"+path, opt.DefaultCompression)
	if err != nil {
		return fmt.Errorf("error loading world: %v", err)
	}

	w := world.Config{
		Dim:      world.Overworld,
		Log:      m.log,
		ReadOnly: true,
		Provider: p,
	}.New()

	w.SetTickRange(0)
	w.SetTime(6000)
	w.StopTime()

	w.StopWeatherCycle()
	w.SetDefaultGameMode(world.GameModeSurvival)
	w.SetBlock(cube.PosFromVec3(mgl64.Vec3{0, 0, 0}), block.Grass{}, nil)

	m.worldsMu.Lock()
	m.worlds[worldName] = w
	m.worldsMu.Unlock()

	log.Debugf(`Loaded world "%v".`, w.Name())
	return nil
}

func (m *Manager) UnloadWorld(w *world.World) error {
	if w == m.DefaultWorld() {
		return fmt.Errorf("the default world cannot be unloaded")
	}

	if _, ok := m.World(w.Name()); !ok {
		return fmt.Errorf("world isn't loaded")
	}

	m.log.Debugf("Unloading world '%v'\n", w.Name())
	for _, p := range m.srv.Players() {
		if p.World() == w {
			m.DefaultWorld().AddEntity(p)
			p.Teleport(m.DefaultWorld().Spawn().Vec3Middle())
		}
	}

	m.worldsMu.Lock()
	delete(m.worlds, w.Name())
	m.worldsMu.Unlock()

	if err := w.Close(); err != nil {
		return fmt.Errorf("error closing world: %v", err)
	}
	m.log.Debugf("Unloaded world '%v'\n", w.Name())
	return nil
}
