package mathutils

import (
	"github.com/go-gl/mathgl/mgl64"
	"math"
)

// MinMax returns the min and max values between 2 numbers.
// ty flash
func MinMax(a, b float64) (min, max int) {
	if a == math.Min(a, b) {
		return int(a), int(b)
	}
	return int(b), int(a)
}

func FloorVec3(vector mgl64.Vec3) mgl64.Vec3 {
	return mgl64.Vec3{math.Floor(vector.X()), math.Floor(vector.Y()), math.Floor(vector.Z())}
}
