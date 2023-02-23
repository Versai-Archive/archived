export default class PacketUtils {
    public static parseJwt(token: string): object {
        let base64Payload = token.split('.')[1];
        let payload = Buffer.from(base64Payload, 'base64');
        return JSON.parse(payload.toString());
    }
}
