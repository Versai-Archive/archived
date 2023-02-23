import discord
from discord.ext import commands
import os
import asyncio
import json

client = commands.Bot(command_prefix="!")
bot = client

class Words(commands.Cog):
    
    def __init__(self, client):
        self.client = client

    @commands.Cog.listener()
    async def on_message(self, message):
      if message.author.bot:
        return
      if message.channel == message.author.dm_channel:
        return
      r = discord.utils.get(message.guild.roles, name='Staff') 
      if r in message.author.roles:
        return

      with open('words.json', 'r') as f:
        file = json.load(f)

      emb = discord.Embed(colour=discord.Colour.blue(),description='You cannot use that word!')
      emb.set_author(name=f'{message.author} | Blacklisted Words', icon_url=message.author.avatar_url)
      
      for x in file['words']:
        if x in message.content.lower().split():
          await message.delete()
          await message.channel.send(embed=emb, delete_after=6)

  
    @commands.command()
    @commands.has_permissions(manage_messages=True)
    async def wordfilter(self, ctx, type=None, arg=None):
      botboi = client.get_user(993534785685295145)
      emb =  discord.Embed(colour=discord.Colour.blue(),timestamp=ctx.message.created_at)
      emb.set_author(name=f'{ctx.author} | Blacklisted Words', icon_url=ctx.author.avatar_url)
      emberror=discord.Embed(colour=discord.Colour.red(),timestamp=ctx.message.created_at)
      emberror.set_author(name='Error',icon_url='https://cdn.discordapp.com/attachments/707870643789627424/732085341275815997/pngegg.png')

      await ctx.message.delete()
      if type is None:
        emberror.description = 'Missing Required Argument'
        await ctx.send(embed=emberror, delete_after=10)
        return
      with open('words.json', 'r') as f:
        file = json.load(f)

      list = []
      if type.lower() == 'add':
        if arg is None:
          emberror.description = 'Missing Required Argument'
          await ctx.send(embed=emberror, delete_after=10)
          return
        x = await check_words(file['words'], arg)
        if x is None:
          emb.description = f'`{arg}` has been added to the list of blacklisted words.'
          await ctx.send(embed=emb, delete_after=10)
          file['words'].append(arg)
        else:
          emb.description = f'`{arg}` is already a blacklisted word.'
          await ctx.send(embed=emb, delete_after=10)

      if type.lower() == 'remove':
        if arg is None:
          emberror.description = 'Missing Required Argument'
          await ctx.send(embed=emberror, delete_after=10)
          return
        x = await check_words(file['words'], arg)
        if x is not None:
          emb.description = f'`{arg}` has been removed from the list of blacklisted words.'
          await ctx.send(embed=emb, delete_after=10)
          file['words'].remove(arg)
        else:
          emb.description = f'`{arg}` is not a blacklisted word.'
          await ctx.send(embed=emb, delete_after=10)

      if type.lower() == 'list':
        if file['words'] == []:
          emb.description = '__Blacklisted Words:__\n\nThere are no blacklisted words.'
          await ctx.send(embed=emb, delete_after=10)
          return
        for x in file['words']:
          Add = "\n"
          New = x
          Final = str(New + Add)
          list.append(Final)
          One_String = " ".join(list)
        emb.description = f'__Blacklisted Words:__\n\n{One_String}'
        await ctx.send(embed=emb, delete_after=30)

      with open('words.json', 'w') as f:
        json.dump(file, f)

    @wordfilter.error
    async def wordfilter_error(self, ctx, error):
      await ctx.message.delete()
      emberror=discord.Embed(colour=discord.Colour.red(),timestamp=ctx.message.created_at)
      emberror.set_author(name='Error',icon_url='https://cdn.discordapp.com/attachments/707870643789627424/732085341275815997/pngegg.png')
      if isinstance(error, commands.CheckFailure):
        emberror.description = 'You do not have permissions to use this command'
        await ctx.send(embed=emberror, delete_after=10)  

async def check_words(list, word):
  for x in list:
    if x == word:
      return x

def setup(client):
    client.add_cog(Words(client))