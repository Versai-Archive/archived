package island

type IslandManager struct{}

var islands = []*Island{}

func New(island *Island) {
	for _, i := range islands {
		if i == island {
			return
		}
	}

	_ = append(islands, island)
}
