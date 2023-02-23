import discord
from discord.ext import commands
import os
import asyncio
import json
import time
import datetime

client = commands.Bot(command_prefix="!")
bot = client

class Afk(commands.Cog):
    
    def __init__(self, client):
        self.client = client

    @commands.Cog.listener()
    async def on_message(self, message):
      with open('afk.json', 'r') as f:
        users = json.load(f)
      
      if message.author == client:
        return
      if message.author.bot:
        return

      botboi = self.client.get_user(993534785685295145)
      if len(message.mentions) > 0:
        player = message.mentions[0]
        x = await check_list(users, player)
        if x is None:
          pass
        else:
          list = []
          length = round(time.time() - users[str(x)]['afktime'])
          day = length // (24 * 3600)
          length = length % (24 * 3600)
          hour = length // 3600
          length %= 3600
          minutes = length // 60
          length %= 60
          seconds = length
          if minutes == 0:
            list.append(f'{seconds} Seconds')
          elif hour == 0:
            list.append(f'{minutes} Minutes and {seconds} Seconds')
          elif day == 0:
            list.append(f'{hour} Hours, {minutes} Minutes and {seconds} Seconds')
          else:
            list.append(f'{day} Days, {hour} Hours, {minutes} Minutes and {seconds} Seconds')
          time_remaining_str = " ".join(list)
          reason = users[str(x)]['reason']
          emb4=discord.Embed(colour=discord.Colour.blue(),description=f'{reason} - {time_remaining_str} ago',timestamp=message.created_at)
          emb4.set_author(name=f'{player} Is AFK',icon_url=player.avatar_url)
          if str(player.id) in users:
            await message.channel.send(embed=emb4,delete_after=10)
      
      if str(message.author.id) in users:
        list = []
        length = round(time.time() - users[str(message.author.id)]['afktime'])
        day = length // (24 * 3600)
        length = length % (24 * 3600)
        hour = length // 3600
        length %= 3600
        minutes = length // 60
        length %= 60
        seconds = length
        if minutes == 0:
          list.append(f'{seconds} Seconds')
        elif hour == 0:
          list.append(f'{minutes} Minutes and {seconds} Seconds')
        elif day == 0:
          list.append(f'{hour} Hours, {minutes} Minutes and {seconds} Seconds')
        else:
          list.append(f'{day} Days, {hour} Hours, {minutes} Minutes and {seconds} Seconds')
        time_remaining_str = " ".join(list)
        emb=discord.Embed(colour=discord.Colour.blue(),description=f'{message.author.mention}Welcome back! You were AFK for **{time_remaining_str}**',timestamp=message.created_at)
        emb.set_author(name=f'{message.author} | AFK',icon_url=message.author.avatar_url)
        await message.channel.send(embed=emb, delete_after=10)
        await remove_user(users, message.author)

      with open('afk.json', 'w') as f:
        json.dump(users, f)

    @commands.command()
    async def afklist(self, ctx):
      with open('afk.json', 'r') as f:
        users=json.load(f)

      botboi = self.client.get_user(993534785685295145)
      if users == {}:
        emb=discord.Embed(colour=discord.Colour.blue(),timestamp=ctx.message.created_at)
        emb.set_author(name='AFK List',icon_url=botboi.avatar_url)
        emb.add_field(name='Members:',value=f'No One Is AFK!',inline=False)
        await ctx.send(embed=emb)
        return

      afk = []
      for user in users:
        Add = "\n"
        New = f'<@{user}>'
        Final = str(New + Add)
        afk.append(Final)
        One_String = " ".join(afk)
        embed=discord.Embed(colour=discord.Colour.blue(),timestamp=ctx.message.created_at)
        embed.set_author(name='AFK List',icon_url=botboi.avatar_url)
        embed.add_field(name='Members:',value=f'{One_String}',inline=False)
      await ctx.send(embed=embed)

    @commands.command()
    async def afk(self, ctx, *,reason=None):
      await ctx.message.delete()
      with open('afk.json', 'r') as f:
        users = json.load(f)

      emb=discord.Embed(colour=discord.Colour.blue(),timestamp=ctx.message.created_at,description=f'{ctx.author} is now AFK!')
      emb.set_author(name=f'{ctx.author} | AFK',icon_url=ctx.author.avatar_url)

      if reason is None:
        reason = 'I am currently AFK - Will be back soon!'
        await add_user(users, ctx.author, reason)
        await ctx.send(embed=emb,delete_after=3)
      else:

        await ctx.send(embed=emb,delete_after=3)
        await add_user(users, ctx.author, reason)

      with open('afk.json', 'w') as f:
        json.dump(users, f)

async def check_list(users, player):
  for x in users:
    if int(x) == int(player.id):
      return x

async def add_user(users, user, reason):
  users[str(user.id)] = {}
  users[str(user.id)]['reason'] = f'{reason}'
  users[str(user.id)]['afktime'] = int(time.time())
  
async def remove_user(users, user):
  if str(user.id) in users:
    del users[str(user.id)]

def setup(client):
    client.add_cog(Afk(client))