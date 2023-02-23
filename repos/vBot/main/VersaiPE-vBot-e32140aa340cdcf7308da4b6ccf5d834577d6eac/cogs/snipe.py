import discord
from discord.ext import commands
import os
import asyncio
import json

client = commands.Bot(command_prefix="!")
bot = client

class Snipe(commands.Cog):
    
    def __init__(self, client):
        self.client = client

    @commands.Cog.listener()
    async def on_message(self, message):
      with open('snipe.json', 'r') as f:
        users = json.load(f)
      
      await guild_update(users, message.guild)
    
      with open("snipe.json", "w") as f:
        json.dump(users, f, indent=4)

    @commands.Cog.listener()
    async def on_message_delete(self, message):
      with open('snipe.json', 'r') as f:
        users = json.load(f)

      if message.author.bot:
        return

      if message.author == client:
        return

      await add_message(users, message.guild, message)
      await add_author(users, message.guild, message.author)

      with open("snipe.json", "w") as f:
        json.dump(users, f, indent=4)

    @commands.command()
    async def snipe(self, ctx):
      with open('snipe.json', 'r') as f:
        users = json.load(f)
      
      msg = users[str(ctx.guild)]['msg']
      id=users[str(ctx.guild)]['author']
      author=self.client.get_user(id)
      if str(ctx.guild) in users:
        embed=discord.Embed(timestamp=ctx.message.created_at,colour=discord.Colour.blue(),description=f'Message: {msg}')
        embed.set_author(name=f'{author}',icon_url=author.avatar_url)
        await ctx.send(embed=embed)

      with open('snipe.json', 'w') as f:
        json.dump(users, f, indent=4)

async def guild_update(users, guild):
  if not str(guild) in users:
    users[str(guild)] ={}
    users[str(guild)]['author'] = 'None'
    users[str(guild)]['msg'] = 'No Message To Snipe'

async def add_message(users, guild, message):
  users[str(guild)]['msg'] = f'{message.content}'

async def add_author(users, guild, author):
  users[str(guild)]['author'] = author.id

def setup(client):
    client.add_cog(Snipe(client))