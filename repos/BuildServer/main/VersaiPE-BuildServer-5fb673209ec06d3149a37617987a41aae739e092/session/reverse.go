package session

import "github.com/VersaiPE/BuildServer/utils"

type ReverseDataHolder struct {
	Undo []utils.BlockStorageHolder
	Redo []utils.BlockStorageHolder
}

func (rdh ReverseDataHolder) SaveUndo(holder utils.BlockStorageHolder) ReverseDataHolder {
	rdh.Undo = append(rdh.Undo, holder)
	return rdh
}

// Idk how to do like ?string in go
func (rdh ReverseDataHolder) NextUndo() (bool, utils.BlockStorageHolder) {
	if len(rdh.Undo) > 0 {
		s := rdh.Undo[len(rdh.Undo)-1]        // get last in slice
		rdh.Undo = rdh.Undo[:len(rdh.Undo)-1] // remove from slice
		return true, s                        // return last
	}
	// Return a nil BlockStorageHolder
	return false, utils.BlockStorageHolder{}
}

func (rdh ReverseDataHolder) SaveRedo(holder utils.BlockStorageHolder) {
	rdh.Redo = append(rdh.Redo, holder)
}

// Idk how to do like ?string in go
func (rdh ReverseDataHolder) NextRedo() (bool, any) {
	if len(rdh.Redo) > 0 {
		s := rdh.Redo[len(rdh.Redo)-1]        // get last in slice
		rdh.Redo = rdh.Redo[:len(rdh.Redo)-1] // remove from slice
		return true, s                        // return last
	}
	return false, nil
}
