package data

import (
	"RPG/session"
	"context"
	"encoding/hex"
	"golang.org/x/crypto/sha3"
	"net/netip"
	"time"
)

const salt = "This IS some Salt lol, it is basically to candy the hashing 235'}AF"

func hashAddress(address string) string {
	addr, _ := netip.ParseAddrPort(address)

	s := sha3.New256()
	s.Write(addr.Addr().AsSlice())
	s.Write([]byte(salt))
	hashed := hex.EncodeToString(s.Sum(nil))
	return hashed
}

func SaveSession(s *session.Session) error {
	p := s.Player
	data := UserData{
		XUID:        p.XUID(),
		Name:        p.Name(),
		Address:     hashAddress(p.Addr().String()),
		Whitelisted: true,
		FirstLogin:  time.Now(),
		PlayTime:    time.Hour,
		CurrentPet:  session.Pet{},
		Pets:        []session.Pet{},
		Skills:      s.Skills,
		Stats:       s.Stats,
	}

	ctx, cls := context.WithTimeout(context.Background(), time.Second*5)
	defer cls()
	//filter := bson.M{"XUID": p.XUID()}
	result, err2 := coll.InsertOne(ctx, data)
	if err2 != nil {
		return err2
	}
	print(result.InsertedID)
	return nil
}
