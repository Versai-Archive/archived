import discord
from discord.ext import commands
import os
import asyncio
import json

client = commands.Bot(command_prefix="!")
bot = client

class Report(commands.Cog):
    
  def __init__(self, client):
    self.client = client

  @commands.command()
  @commands.cooldown(1, 20, commands.BucketType.user)
  async def report(self, ctx, member : discord.Member, *, reason):
    botboi = self.client.get_user(993534785685295145)
    log = self.client.get_channel(1018652128547454997)
    emb = discord.Embed(colour = discord.Colour.blue(), timestamp=ctx.message.created_at, description = f'You have successfully reported `{member}` for `{reason}`')
    emb.set_footer(icon_url = botboi.avatar_url, text='Versai Network')
    emb.set_author(name=f'{ctx.author} | Report',icon_url=ctx.author.avatar_url)

    emb2 = discord.Embed(colour = discord.Colour.blue(), timestamp=ctx.message.created_at, description = f'Member Reported: {member}\nReporter: {ctx.author}\nReason: {reason}')
    emb2.set_footer(icon_url = botboi.avatar_url, text='Versai Network')
    emb2.set_author(name='Reports',icon_url=botboi.avatar_url)

    await ctx.message.delete()
    await log.send(embed=emb2)
    try:
      await ctx.author.send(embed=emb)
    except:
      print(f'Could not message {ctx.author}')
      

  @report.error
  async def report_error(self, ctx, error):
    await ctx.message.delete()
    emberror=discord.Embed(colour=discord.Colour.red(),timestamp=ctx.message.created_at)
    emberror.set_author(name='Error',icon_url='https://cdn.discordapp.com/attachments/707870643789627424/732085341275815997/pngegg.png')
    if isinstance(error, commands.MissingRequiredArgument):
      emberror.description = 'Invalid Usage\n\nUsage: `!report <user> <reason>`'
      await ctx.send(embed=emberror, delete_after=10)  
    elif isinstance(error, commands.CommandOnCooldown):
      emberror.description = f"This command is currently on cooldown for {round(error.retry_after, 2)} seconds."
      await ctx.send(embed=emberror, delete_after=10)


    

def setup(client):
  client.add_cog(Report(client))