import { Packet } from 'bdsx/bds/packet';
import { nativeClass, nativeField } from 'bdsx/nativeclass';
import { int64_as_float_t, bool_t } from 'bdsx/nativetype';

@nativeClass(null)
export default class NetworkStackLatencyPacket extends Packet {
    @nativeField(int64_as_float_t)
    public timestamp: int64_as_float_t;
    @nativeField(bool_t)
    public needsResponse: bool_t;
}