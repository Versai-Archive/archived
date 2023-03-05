const Discord = require("discord.js");
const emojis = require("../../storage/emojis.json");
const ytdl = require("ytdl-core");
const ytsearch = require("yt-search");
const queue = new Map();

class play {
  constructor() {
    this.name = "play";
    this.aliases = [];
    this.description = "Play music/videos from YouTube";
    this.usage = ["play <search|url>"];
    this.category = "Music";
  }

  async onRun(client, msg, args) {
    //#region Embeds
    const notInVC = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} You are not in a voice channel.`);
    const noPermsBotEmbed = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} I do not have permission to speak in this voice channel.`
      );
    const noPermsBotEmbed2 = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} I do not have permission to join this voice channel.`
      );
    const noSong = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} Provide a song for me to play.`);
    const videoError = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(
        `${emojis.crossmark} There was an error getting this video.`
      );
    const connectionError = new Discord.MessageEmbed()
      .setColor("#2F3136")
      .setDescription(`${emojis.crossmark} There was an error connecting.`);
    //#endregion

    const videoPlayer = async (guild, song) => {
      const songQueue = queue.get(guild.id);
      if (!song) {
        songQueue.voice_channel.leave();
        queue.delete(guild.videoDetails);
        return;
      }

      const songPlaying = new Discord.MessageEmbed()
        .setColor("#2F3136")
        .setDescription(`${emojis.checkmark} Now playing **${song.title}**!`);

      const stream = ytdl(song.url, { filter: "audioonly" });
      songQueue.connection
        .play(stream, { seek: 0, volume: 0.5 })
        .on("finish", () => {
          songQueue.songs.shift();
          videoPlayer(guild, songQueue.songs[0]);
        });
      await msg.channel.send(songPlaying);
    };

    if (!msg.member.voice.channelID) return msg.channel.send(notInVC);
    if (!args[0]) return msg.channel.send(noSong);

    let vc = msg.guild.channels.cache.get(msg.member.voice.channelID);
    if (!vc.permissionsFor(client.user.id).has("SPEAK"))
      return msg.channel.send(noPermsBotEmbed);
    if (!vc.permissionsFor(client.user.id).has("CONNECT"))
      return msg.channel.send(noPermsBotEmbed2);

    const serverQueue = queue.get(msg.guild.id);
    let song = {};

    if (ytdl.validateURL(args[0])) {
      const songInfo = await ytdl.getInfo(args[0]);
      song = {
        title: songInfo.videoDetails.title,
        url: songInfo.videoDetails.video_url,
      };
    } else {
      const finder = async (query) => {
        const videoResult = await ytsearch(query);
        return videoResult.videos.length > 1 ? videoResult.videos[0] : null;
      };
      const video = await finder(args.join(" "));
      if (video) {
        song = { title: video.title, url: video.url };
      } else return msg.channel.send(videoError);
    }

    if (!serverQueue) {
      const queueConstructor = {
        voice_channel: vc,
        text_channel: msg.channel,
        connection: null,
        songs: [],
      };
      queue.set(msg.guild.id, queueConstructor);
      queueConstructor.songs.push(song);

      try {
        const connection = await vc.join();
        queueConstructor.connection = connection;
        videoPlayer(msg.guild, queueConstructor.songs[0]);
      } catch (err) {
        queue.delete(msg.guild.id);
        msg.channel.send(connectionError);
        throw err;
      }
    } else {
      const songAdded = new Discord.MessageEmbed()
        .setColor("#2F3136")
        .setDescription(
          `${emojis.checkmark} **${song.title}** has been added to the queue!`
        );
      serverQueue.songs.push(song);
      return msg.channel.send(songAdded);
    }
  }
}

module.exports = play;
