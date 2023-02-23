import { XYZ } from "./types/level/XYZ";

export default class CoordinateUtil {
    public static intersects(a: XYZ, b: XYZ, epsilon = 0.000001): boolean {
        if (Math.max(...b.x) - Math.min(...a.x) > epsilon && Math.max(...a.x) - Math.min(...b.x) > epsilon) {
            if (Math.max(...b.y) - Math.min(...a.y) > epsilon && Math.max(...a.y) - Math.min(...b.y)> epsilon) {
                return Math.max(...b.z) - Math.min(...a.z) > epsilon && Math.max(...a.z) - Math.min(...b.z) > epsilon;
            }
        }
        return false;
    }

    public static between(pos: VectorXYZ, area: XYZ): boolean {
        const minX = Math.min(...area.x);
        const minY = Math.min(...area.y);
        const minZ = Math.min(...area.z);
        const maxX = Math.max(...area.x);
        const maxY = Math.max(...area.y);
        const maxZ = Math.max(...area.z);
        if(
            pos.x >= minX && pos.x <= maxX &&
            pos.y >= minY && pos.y <= maxY &&
            pos.z >= minZ && pos.z <= maxZ
        ) {
            return true;
        } else {
            return false;
        }
    }
}