export default class VoiceState {
	public userID: string;
	public channelID: string;
	public sessionID: string;
	public deaf: boolean;
	public mute: boolean;
	public suppress: boolean;
	public selfDeaf: boolean;
	public selfMute: boolean;
	public selfVideo: boolean;

	constructor(data: any) {
		this.userID = data.user_id;
		this.channelID = data.channel_id;
		this.sessionID = data.session_id;
		this.deaf = data.deaf;
		this.mute = data.selfMute;
		this.suppress = data.suppress;
		this.selfDeaf = data.self_deaf;
		this.selfMute = data.self_mute;
		this.selfVideo = data.self_video;
	}
}
