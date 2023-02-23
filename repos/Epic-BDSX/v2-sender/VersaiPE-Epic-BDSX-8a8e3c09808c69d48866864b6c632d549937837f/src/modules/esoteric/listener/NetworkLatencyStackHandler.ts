import NetworkStackLatencyPacket from '../packet/NetworkStackLatencyPacket';

export default class NetworkLatencyStackHandler {
    private static list = [];

    public static random(needsResponse: boolean = true): NetworkStackLatencyPacket {
        let pk = new NetworkStackLatencyPacket
        pk.needsResponse = needsResponse;
        pk.timestamp = Math.floor(Math.random() * 1000000000000000);
        return pk;
    }
}