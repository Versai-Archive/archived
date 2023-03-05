import NextAuth from "next-auth";
import Providers from "next-auth/providers";

const options = {
  providers: [
    Providers.Discord({
      clientId: process.env.DISCORD_CLIENT_ID,
      clientSecret: process.env.DISCORD_CLIENT_SECRET,
      scope: ["guilds", "identify"],
      async profile(profile, tokens) {
        return {
          id: profile.id,
          name: profile.username,
          email: profile.email,
          image: `https://cdn.discordapp.com/avatars/${profile.id}/${profile.avatar}`,
        };
      },
    }),
  ],
  callbacks: {
    async signIn(user, account, profile) {
      let guildsRes;
      let guilds;
      if (account.accessToken) {
        guildsRes = await fetch("https://discord.com/api/users/@me/guilds", {
          headers: {
            Authorization: "Bearer " + account.accessToken,
          },
        });

        guilds = await guildsRes.json();
        if (
          await guilds.some((element) => element.id === "686340569794215945")
        ) {
          return true;
        }
        return false;
      }
    },
  },
};

export default (req, res) => NextAuth(req, res, options);
