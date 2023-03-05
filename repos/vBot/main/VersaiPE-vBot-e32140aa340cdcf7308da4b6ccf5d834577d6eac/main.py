import discord
from discord.ext import commands
from discord import Webhook, RequestsWebhookAdapter
import json
from pathlib import Path
import logging
import datetime
import asyncio
import os
import time
import random
import sys
import re
import requests
import urllib.request
import urllib
from glob import glob

os.chdir(r'Versai')

intents = discord.Intents().all()

cwd = Path(__file__).parents[0]
cwd = str(cwd)

client = commands.Bot(command_prefix='!', intents=intents, help_command=None)
bot = client

@client.event
async def on_ready():
    print('Versai Bot Online')

@client.event
async def on_guild_channel_create(channel):
  guild = await client.fetch_guild(708291410612322345) #guild id
  botboi = client.get_user(993534785685295145) #bot id
  category = client.get_channel(783030302196629564) #category id
  if channel.category == category:
    await asyncio.sleep(2)
    emb = discord.Embed(colour=discord.Colour.blue())
    emb.set_author(name='Versai-Applications', icon_url=botboi.avatar_url) 
    emb.description = 'Do you agree with the following terms before proceding with your appliction?\n\n- You have a working microphone and can participate in a voicechat interview\n- You are above the age of 14\n- You are active on the server'
    msg = await channel.send(embed=emb)
    await msg.add_reaction('✅')
    await msg.add_reaction('❌')

    def check1(reaction, user):
      return user != botboi
    
    reaction, user = await client.wait_for('reaction_add', check=check1)
    await msg.clear_reactions()
    if str(reaction) == '❌':
      emb.description = "Since you do not agree to our terms, your application has been terminated."
      await msg.edit(embed=emb)
      r = guild.get_role(708300969867345951) #role id to mention 
      await channel.send(r.mention)
      return
      
    emb.description = 'React to the corresponding reaction to apply for a position.\n\n🔴: PVP\n🟠: Survival\n🟡: Developer\n🟢: Builder'
    await msg.edit(embed=emb)
    await msg.add_reaction('🔴')
    await msg.add_reaction('🟠')
    await msg.add_reaction('🟡')
    await msg.add_reaction('🟢')
  else:
    pass  

@client.event
async def on_raw_reaction_add(payload):
  guild = discord.utils.find(lambda g : g.id == payload.guild_id, client.guilds)
  botboi = client.get_user(993534785685295145) #bot id

  if payload.member == botboi:
    return
  
  def check(m):
    return m.author == payload.member and m.channel.id == payload.channel_id

  channel = client.get_channel(payload.channel_id)
  rmsg = await channel.fetch_message(payload.message_id)
  emb = discord.Embed(colour=discord.Colour.blue())

  pvpquestions = ['Question 1: Do you have a working microphone?', 'Question 2: How old are you?', 'Question 3: What is your in game name?', 'Question 4: What alts have you used on Versai? This will be checked so be honest.', 'Question 5: What is your time zone?', 'Question 6: What languages can you speak?', 'Question 7: What platforms do you play on? (Console, iOS, Android, Windows 10).', 'Question 8: Does anyone else use your device or account(both discord and in game)?', 'Question 9: Have you ever been punished from the Versai Network or Discord server? If so, list the banned IGN(s) and the reason(s).', 'Question 10: Name three staff members who you believe do their job well and explain why you have selected them. (If you only know 1-2 staff members then name them)', 'Question 11: Name three staff members that you believe are not doing a good job and state the reasons.', 'Question 12: Is your device capable of recording Minecraft footage?', 'Question 13: What is your availability? Please list an estimate of the hours you can play each day of the week.', 'Question 14: Do you have any past staffing experience (explain in detail your roles and responsibilities, if any)?', 'Question 15: Are you able to help bring additional players to Versai? If so, how?', 'Question 16:  If you are in a 1v1, and a player tells you that the person they are fighting is suspicious, what would you do?', 'Question 17: You are being messaged by two players. One is asking for you to teleport to a hacker and the other is swearing at you for no reason. What would you do?', 'Question 18: What would you do if you suspect a staff member is cheating?', 'Question 19: You see a staff member abusing either in game or in discord what do you do?', 'Question 20: How do you approach a situation where a staff member is caught cheating?', 'Question 21: What does being staff mean to you?', 'Question 22: Why do you want to become staff in Versai?', 'Question 23: What else should we know about you?']
  survivalquestions = ['Question 1: What is your age?', 'Question 2: What is your IGN?', 'Question 3: What alts have you used on the survival server?', 'Question 4: What makes you want to apply for survival staff?', 'Question 5: How often do you play on the survival servers?', 'Question 6: Why should we consider you over other survival applicants?', 'Question 7:  What would you do if you seen someone cheating?', 'Question 8: What would you do if someone was swearing continuously?', 'Question 9: How would you go about seeing if someone is using toolbox to give themselves items?', 'Question 10: Have you ever been banned before on the Versai network?', 'Question 11: What is your timezone?', 'Question 12:  Is there anything else you would like to share?']
  devquestions = ['Question 1: What is your age?', 'Question 2: What is your IGN?', 'Question 3: Why do you want to be a developer?', 'Question 4: Have you ever worked as a developer for any other server? if yes please mention what you had done for that server briefly', 'Question 5: Can you name some of the plugins you have made?', 'Question 6: Can you please send your github account?']
  builderquestions = ['Question 1: What is your IGN? ', 'Question 2: What is your age?', 'Question 3: What is your timezone?', 'Question 4: How experienced are you with World Edit and Block Sniper?', 'Question 5: What are your strengths and weaknesses in building? (ie: Terrain, Structures, etc.)', 'Question 6: Have you been on any former build teams/organizations? Are you working for any right now?']
  
  if str(payload.emoji) == '🔴': 
    list = []
    await rmsg.delete()
    emb.set_author(name=f"{payload.member}'s PvP Application", icon_url=payload.member.avatar_url)
    role = discord.utils.get(guild.roles, name='›› Recruitment | Team ‹‹')
    await channel.set_permissions(role, read_messages=True, send_messages=True)
    for x in pvpquestions:
      emb.description = x
      try:
        await msg.edit(embed=emb)
      except:
        msg = await channel.send(embed=emb)
      r = await client.wait_for('message', check=check)
      await r.delete()
      list.append(f'{x}\n\nAnswer: {r.content}')
    await msg.delete()
    for y in list:
      emb.description = y
      await channel.send(embed=emb)
      await asyncio.sleep(1)
    
      
  if str(payload.emoji) == '🟠': 
    list = []
    await rmsg.delete()
    emb.set_author(name=f"{payload.member}'s Survival Application", icon_url=payload.member.avatar_url)
    role1 = discord.utils.get(guild.roles, name='Survival Staff Manager')
    await channel.set_permissions(role1, read_messages=True, send_messages=True)
    role = discord.utils.get(guild.roles, name='Survival Team')
    await channel.set_permissions(role, read_messages=True, send_messages=True)
    for x in survivalquestions:
      emb.description = x
      try:
        await msg.edit(embed=emb)
      except:
        msg = await channel.send(embed=emb)
      r = await client.wait_for('message', check=check)
      await r.delete()
      list.append(f'{x}\n\nAnswer: {r.content}')
    await msg.delete()
    for y in list:
      emb.description = y
      await channel.send(embed=emb)
      await asyncio.sleep(1)
      
  if str(payload.emoji) == '🟡': 
    list = []
    await rmsg.delete()
    emb.set_author(name=f"{payload.member}'s Developer Application", icon_url=payload.member.avatar_url)
    role = discord.utils.get(guild.roles, name='Network Developer')
    await channel.set_permissions(role, read_messages=True, send_messages=True)
    for x in devquestions:
      emb.description = x
      try:
        await msg.edit(embed=emb)
      except:
        msg = await channel.send(embed=emb)
      r = await client.wait_for('message', check=check)
      await r.delete()
      list.append(f'{x}\n\nAnswer: {r.content}')
    await msg.delete()
    for y in list:
      emb.description = y
      await channel.send(embed=emb)
      await asyncio.sleep(1)
      
  if str(payload.emoji) == '🟢':
    list = []
    await rmsg.delete()
    emb.set_author(name=f"{payload.member}'s Builder Application", icon_url=payload.member.avatar_url)
    #next 2 lines very important
    role = discord.utils.get(guild.roles, name='Senior Builder')
    await channel.set_permissions(role, read_messages=True, send_messages=True)
    role1 = discord.utils.get(guild.roles, name='Build Staff Manager')
    await channel.set_permissions(role1, read_messages=True, send_messages=True)



    for x in builderquestions:
      emb.description = x
      try:
        await msg.edit(embed=emb)
      except:
        msg = await channel.send(embed=emb)
      r = await client.wait_for('message', check=check)
      await r.delete()
      list.append(f'{x}\n\nAnswer: {r.content}')
    await msg.delete()
    for y in list:
      emb.description = y
      await channel.send(embed=emb)
      await asyncio.sleep(1)

@client.command()
async def help(ctx):

  botboi=client.get_user(993534785685295145)
  main = discord.Embed(colour=discord.Colour.blue(),description='Versai Bot is a custom made bot specifically for this server. If you ever run into any problems with the bot, create a ticket. Click the next page to view the command list.')
  main.set_author(name='Versai Bot Help Menu',icon_url=botboi.avatar_url)
  main.set_footer(text=f'Ran By {ctx.author}',icon_url=ctx.author.avatar_url)

  main2 = discord.Embed(colour=discord.Colour.blue())
  main2.add_field(name='General Commands',value=f'!afk\n!afklist\n!gamelb\n!snipe\n!report',inline=True)
  main2.add_field(name='Moderator Commands',value=f'!wordfilter\n!jail\n!unjail\n!jaillookup',inline=False)

  main2.set_author(name='Versai Bot Help Menu',icon_url=botboi.avatar_url)
  main2.set_footer(text=f'Ran By {ctx.author}',icon_url=ctx.author.avatar_url)

  emoji= '⬅️'
  emoji2= '➡️'

      
  embed = await ctx.send(embed=main)
  await embed.add_reaction(emoji)
  await embed.add_reaction(emoji2)

  def check(reaction, user):
    return user == ctx.author and str(reaction.emoji) == emoji2

  try:
    reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
  except asyncio.TimeoutError:
        
    return
  else:
    embed2 = await embed.edit(embed=main2)

    def check(reaction, user):
      return user == ctx.author and str(reaction.emoji) == emoji

    try:
      reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
    except asyncio.TimeoutError:
          
      return
    else:
      await embed.edit(embed=main)

      def check(reaction, user):
        return user == ctx.author and str(reaction.emoji) == emoji2

      try:
        reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
      except asyncio.TimeoutError:
            
        return
      else:
        await embed.edit(embed=main2)

        def check(reaction, user):
          return user == ctx.author and str(reaction.emoji) == emoji

        try:
          reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
        except asyncio.TimeoutError:
              
          return
        else:
          await embed.edit(embed=main)
          def check(reaction, user):
            return user == ctx.author and str(reaction.emoji) == emoji2

          try:
            reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
          except asyncio.TimeoutError:
                
            return
          else:
            await embed.edit(embed=main2)
            def check(reaction, user):
              return user == ctx.author and str(reaction.emoji) == emoji

            try:
              reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
            except asyncio.TimeoutError:
                  
              return
            else:
              await embed.edit(embed=main)
              def check(reaction, user):
                return user == ctx.author and str(reaction.emoji) == emoji2

              try:
                reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
              except asyncio.TimeoutError:
                    
                return
              else:
                await embed.edit(embed=main2)
                def check(reaction, user):
                  return user == ctx.author and str(reaction.emoji) == emoji

                try:
                  reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
                except asyncio.TimeoutError:
                      
                  return
                else:
                  await embed.edit(embed=main)
                  def check(reaction, user):
                    return user == ctx.author and str(reaction.emoji) == emoji2

                  try:
                    reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
                  except asyncio.TimeoutError:
                        
                    return
                  else:
                    await embed.edit(embed=main2)
                    def check(reaction, user):
                      return user == ctx.author and str(reaction.emoji) == emoji

                    try:
                      reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
                    except asyncio.TimeoutError:
                          
                      return
                    else:
                      await embed.edit(embed=main)
                      def check(reaction, user):
                        return user == ctx.author and str(reaction.emoji) == emoji2

                      try:
                        reaction, user = await client.wait_for('reaction_add', timeout=30.0, check=check)
                      except asyncio.TimeoutError:
                          
                        return
                      else:
                        await embed.delete()
  
if __name__ == '__main__':
    for file in os.listdir(cwd+"/cogs"):
        if file.endswith(".py") and not file.startswith("_"):
            bot.load_extension(f"cogs.{file[:-3]}")
          
client.run('OTkzNTM0Nzg1Njg1Mjk1MTQ1.GdDJsE._D3Zi1-Xi8W3PLHfbghpt8bQP5UntvrFWSlGAE')