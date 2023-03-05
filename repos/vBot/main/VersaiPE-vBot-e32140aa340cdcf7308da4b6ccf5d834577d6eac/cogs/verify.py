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

class Verify(commands.Cog):    
    def __init__(self, client):
        self.client = client
        self.client.wait_until_ready

    @commands.command()
    @commands.is_owner()
    async def verifyrefresh(ctx):
        botboi = self.client.get_user(993534785685295145)
        chan = self.client.get_channel(724041568343949386)
        msg = await chan.fetch_message(id=1023145922433138739)
        emb = discord.Embed(colour=discord.Colour.blue(), description='React below to gain full access to the server!')
        emb.set_author(name='Versai Network', icon_url=botboi.avatar_url)
        await ctx.message.delete()
        await msg.edit(embed=emb)
        await msg.add_reaction('✔️')

    @commands.Cog.listener()
    async def on_raw_reaction_add(self, payload):
        member = payload.member
        chan = self.client.get_channel(724041568343949386)
        msg = await chan.fetch_message(id=1023145922433138739)
        guild = discord.utils.find(lambda g : g.id == payload.guild_id, self.client.guilds)

        r = discord.utils.get(guild.roles, name='Verified')
  
        if member.bot:
            return
        if not payload.message_id == msg.id:
            return
        
        if str(payload.emoji) == '✔️':
            await msg.remove_reaction('✔️',member)
            await member.add_roles(r)

def setup(client):
    t = Verify(client)
    client.add_cog(t)
