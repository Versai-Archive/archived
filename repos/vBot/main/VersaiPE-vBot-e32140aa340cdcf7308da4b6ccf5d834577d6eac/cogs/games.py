import discord
from discord.ext import commands
import os
import asyncio
import time
import datetime
import random
import json

client = commands.Bot(command_prefix="!")
bot = client

giveaways = []

Message_IDS = []

class Games(commands.Cog):    
    def __init__(self, client):
        self.client = client
        self.client.wait_until_ready

    @commands.Cog.listener()
    async def on_ready(self):

      self.client.loop.create_task(on_ready_loop(self))

    @commands.command()
    async def gamelb(self, ctx):
      botboi = self.client.get_user(993534785685295145)
      emb=discord.Embed(colour=discord.Colour.blue(),description='Fetching Rank Leaderboard - This May Take Some Time',timestamp=ctx.message.created_at)
      emb.set_author(name=f'{ctx.author} | Game Leaderboard', icon_url=ctx.author.avatar_url)
      await ctx.message.delete()
      msg = await ctx.send(embed=emb)
      await asyncio.sleep(2)
      with open('gamewins.json', 'r') as f:
        users = json.load(f)
      highscore = sorted(users, key=lambda x : users[x].get('wins', 0), reverse=True)
      for number, user in enumerate(highscore[:10]):
        member = await self.client.fetch_user(user)
        embed = discord.Embed(colour=discord.Colour.blue())
        embed.set_author(name='Top Game Wins')
        embed.set_thumbnail(url=botboi.avatar_url)
      for number, user in enumerate(highscore[:10]):
        member = await self.client.fetch_user(user)
        wins = users[str(member.id)]['wins']
        embed.add_field(name=f'{number + 1}: {member.name}',value=f'Wins: {wins}\n',inline=False)
      await msg.edit(embed=embed)

async def on_ready_loop(self):
  await self.client.wait_until_ready()
  while True:
    randomnum = random.randint(3600,7200)
    await asyncio.sleep(randomnum)
    #await asyncio.sleep(10) 
    #return

    emb = discord.Embed(colour=discord.Colour.blue())
    channel = self.client.get_channel(708293571236593725) #General Channel
    botboi = self.client.get_user(993534785685295145)
    odds = random.randint(1,100)
    def check(m):
      return m.channel == channel
      
    if odds >= 1 and odds <= 33:
      secretnumber = random.randint(0,10)
      emb.set_author(name=f'Number Game', icon_url=botboi.avatar_url)
      emb.description = 'Guess That Number! First One To Guess A Number Between 0-10 Wins!'

      msg = await channel.send(embed=emb)
      await channel.edit(slowmode_delay=2)
      try:  
        while True:
          r = await self.client.wait_for('message', timeout=300 ,check=check)
          try:
            guess = int(r.content)
          except:
            continue 
          
          if guess > secretnumber:
            continue
          elif guess < secretnumber:
            continue
          elif guess == secretnumber:
            with open('gamewins.json', 'r') as f:
              users = json.load(f)
      
            await update_data(users, r.author)   
            await add_wins(users, r.author, 1)

            with open('gamewins.json', 'w') as f:
              json.dump(users, f)

            emb.description = f'`{r.author}` Has Won! The Number Was `{guess}`'
            await msg.edit(embed=emb)
            await channel.edit(slowmode_delay=0)
            break
      
      except asyncio.TimeoutError:
        emb.description = 'No one guessed the number.'
        await msg.edit(embed=emb,delete_after=10)
        await channel.edit(slowmode_delay=0)
        
    elif odds >= 34 and odds <= 67:
      word_list = ['calculator', 'support', 'excited', 'surround', 'popcorn', 'supreme', 'mountain', 'suggestion', 'discord', 'knock', 'quiet', 'river', 'cobweb', 'circle', 'potato', 'telephone', 'confused', 'outstanding', 'transport', 'forest', 'riddle', 'command', 'venomous', 'borrow', 'sloppy', 'effect', 'perfect', 'reduce', 'leather', 'imagine', 'adjustment', 'introduce', 'courageous', 'window', 'allow', 'lovely', 'hateful', 'trouble', 'enter', 'shiver', 'muscle', 'threat', 'spotted', 'grease', 'share', 'whole', 'royal', 'fact', 'thirsty', 'welcome']

      word = random.choice(word_list)
      l = list(word)
      random.shuffle(l)
      scrambled = ''.join(l)
      emb.set_author(name=f'Unscramble Game', icon_url=botboi.avatar_url)
      emb.description = f'First One To Unscramble The Following Word Wins!\n\n`{scrambled}`'
      msg = await channel.send(embed=emb)
      await channel.edit(slowmode_delay=2)
      try:
        while True:
          r = await self.client.wait_for('message', timeout=300, check=check)
          if not r.content == word:
            continue
          else:
            with open('gamewins.json', 'r') as f:
              users = json.load(f)
      
            await update_data(users, r.author)
            await add_wins(users, r.author, 1)

            with open('gamewins.json', 'w') as f:
              json.dump(users, f)

            emb.description = f'`{r.author}` Has Won! The Word Was `{word}`'
            await msg.edit(embed=emb)
            await channel.edit(slowmode_delay=0)
            break
      except asyncio.TimeoutError:
        emb.description = 'No one unscrambled the word.'
        await msg.edit(embed=emb, delete_after=10)
        await channel.edit(slowmode_delay=0)
      
    elif odds >= 68 and odds <= 100:
      reaction_list = ['⬜', '🟥', '🟧', '🟦', '🟩']
      reaction = random.choice(reaction_list)
      emb.set_author(name=f'Reaction Game', icon_url=botboi.avatar_url)
      emb.description = f'Loading Reaction Game...'
      msg = await channel.send(embed=emb)
      for x in reaction_list:
        await msg.add_reaction(x)
      await asyncio.sleep(2)
      emb.description = f'First one to react to {reaction} wins!'
      await msg.edit(embed=emb)
      try:
        _, user = await self.client.wait_for("reaction_add", check=lambda _reaction, user: _reaction.message.channel == channel and _reaction.message == msg and str(_reaction.emoji) == reaction and user != self.client.user and not user.bot, timeout=300)
        with open('gamewins.json', 'r') as f:
          users = json.load(f)
      
        await update_data(users, user)
        await add_wins(users, user, 1)

        with open('gamewins.json', 'w') as f:
          json.dump(users, f)

        emb.description = f'`{user}` Has Won!'
        await msg.edit(embed=emb)
      except asyncio.TimeoutError:
        emb.description = 'No one reacted to the message.'
        await msg.edit(embed=emb, delete_after=10)



async def update_data(users, user):
  if not str(user.id) in users:
    users[str(user.id)] = {}
    users[str(user.id)]['wins'] = 0

async def add_wins(users, user, win):
  users[str(user.id)]['wins'] += win

def setup(client):
  t = Games(client)
  client.add_cog(t)