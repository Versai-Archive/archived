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

class Jail(commands.Cog):    
    def __init__(self, client):
        self.client = client
        self.client.wait_until_ready
    
    @commands.command()
    @commands.has_role('Senior Team')
    async def jail(self, ctx, member:discord.Member, *, reason):
        await ctx.message.delete()
        with open('jail.json', 'r') as f:
            jail = json.load(f)
        
        rlist = []
        rjail = discord.utils.get(ctx.guild.roles, name='Jailed')
        log = self.client.get_channel(708294279163936891)
        botboi = self.client.get_user(993534785685295145)
        emb = discord.Embed(colour=discord.Colour.red())
        emb.set_author(name=f'{ctx.author} | Jail', icon_url=ctx.author.avatar_url)
        
        if rjail in member.roles:
            emb.description = f'`{member}` is already jailed.'
            await ctx.send(embed=emb, delete_after=10)
            return

        for r in member.roles:
            if str(r.name) == str('@everyone'):
                pass
            else:
                rlist.append(r.id)
                await member.remove_roles(r)
                await member.add_roles(rjail)
                await add_member(jail, member, reason, ctx.author, rlist)

        emb.description  = f'You have successfully jailed `{member}` for `{reason}`'
        await ctx.send(embed=emb, delete_after=10)

        emb.set_author(name=f'{member} Jailed', icon_url=member.avatar_url)
        emb.description = f'A member has been jailed.\n\nUser: `{member}`\nStaff: `{ctx.author}`\nReason:`{reason}`'
        await log.send(embed=emb)

        with open('jail.json', 'w') as f:
            json.dump(jail, f)


    @commands.command()
    @commands.has_role('Senior Team')
    async def unjail(self, ctx, member:discord.Member):
        await ctx.message.delete()
        with open('jail.json', 'r') as f:
            jail = json.load(f)
        
        rjail = discord.utils.get(ctx.guild.roles, name='Jailed')
        log = self.client.get_channel(708294279163936891)
        botboi = self.client.get_user(993534785685295145)
        emb = discord.Embed(colour=discord.Colour.red())
        emb.set_author(name=f'{ctx.author} | Jail', icon_url=ctx.author.avatar_url)
        
        await unjail(jail, member, ctx.guild)
        await member.remove_roles(rjail)

        emb.description  = f'You have successfully un-jailed `{member}`'
        await ctx.send(embed=emb, delete_after=10)

        emb.set_author(name=f'{member} Un-Jailed', icon_url=member.avatar_url)
        emb.description = f'A member has been un-jailed.\n\nUser: `{member}`\nStaff: `{ctx.author}`'
        await log.send(embed=emb)

        with open('jail.json', 'w') as f:
            json.dump(jail, f)


    @commands.command()
    @commands.has_role('Senior Team')
    async def jaillookup(self, ctx, member:discord.Member):
        await ctx.message.delete()
        with open('jail.json', 'r') as f:
            jail = json.load(f)
        
        rjail = discord.utils.get(ctx.guild.roles, name='Jailed')
        botboi = self.client.get_user(993534785685295145)
        emb = discord.Embed(colour=discord.Colour.red())
        emb.set_author(name=f'{ctx.author} | Jail', icon_url=ctx.author.avatar_url)

        if not rjail in member.roles:
            emb.description = f'`{member}` is not jailed.'
            await ctx.send(embed=emb, delete_after=10)
            return
        
        staff = jail[str(member.id)]['staff']
        reason = jail[str(member.id)]['reason']
        emb.set_author(name=f"{member}'s History", icon_url=member.avatar_url)
        emb.description = f'Staff: `{staff}`\nReason: `{reason}`'
        await ctx.send(embed=emb, delete_after=30)


    @commands.Cog.listener()
    async def on_member_ban(self, guild, member:discord.Member):
        with open('jail.json', 'r') as f:
            jail = json.load(f)
            
        x = await jail_lookup(jail, member)
        if x is not None:
            del jail[str(member.id)]  

        with open('jail.json', 'w') as f:
            json.dump(jail, f)

    @commands.Cog.listener()
    async def on_member_join(self, member:discord.Member):
        with open('jail.json', 'r') as f:
            jail = json.load(f)

        guild = self.client.get_guild(708291410612322345)
        rjail = discord.utils.get(guild.roles, name='Jailed')    
        x = await jail_lookup(jail, member)
        if x is not None:
            await member.add_roles(rjail)  

        with open('jail.json', 'w') as f:
            json.dump(jail, f)

    @jail.error
    async def jail_error(self, ctx, error):
        await ctx.message.delete()
        emberror=discord.Embed(colour=discord.Colour.red(),timestamp=ctx.message.created_at)
        emberror.set_author(name='Error',icon_url='https://cdn.discordapp.com/attachments/707870643789627424/732085341275815997/pngegg.png')
        if isinstance(error, commands.MissingRequiredArgument):
            emberror.description = 'Invalid Usage\n\nUsage: `!jail <user> <reason>`'
            await ctx.send(embed=emberror, delete_after=10)  
        if isinstance(error, commands.MissingPermissions):
            emberror.description = f"You must be a member of the Senior Staff Team to use this command."
            await ctx.send(embed=emberror, delete_after=10)

    @unjail.error
    async def unjail_error(self, ctx, error):
        await ctx.message.delete()
        emberror=discord.Embed(colour=discord.Colour.red(),timestamp=ctx.message.created_at)
        emberror.set_author(name='Error',icon_url='https://cdn.discordapp.com/attachments/707870643789627424/732085341275815997/pngegg.png')
        if isinstance(error, commands.MissingRequiredArgument):
            emberror.description = 'Invalid Usage\n\nUsage: `!unjail <user>`'
            await ctx.send(embed=emberror, delete_after=10)  
        if isinstance(error, commands.MissingPermissions):
            emberror.description = f"You must be a member of the Senior Staff Team to use this command."
            await ctx.send(embed=emberror, delete_after=10)

    @jaillookup.error
    async def jaillookup_error(self, ctx, error):
        await ctx.message.delete()
        emberror=discord.Embed(colour=discord.Colour.red(),timestamp=ctx.message.created_at)
        emberror.set_author(name='Error',icon_url='https://cdn.discordapp.com/attachments/707870643789627424/732085341275815997/pngegg.png')
        if isinstance(error, commands.MissingRequiredArgument):
            emberror.description = 'Invalid Usage\n\nUsage: `!jaillookup <user>`'
            await ctx.send(embed=emberror, delete_after=10)  
        if isinstance(error, commands.MissingPermissions):
            emberror.description = f"You must be a member of the Senior Staff Team to use this command."
            await ctx.send(embed=emberror, delete_after=10)

async def add_member(jail, user, reason, staff, roles):
    jail[str(user.id)] = {}
    jail[str(user.id)]['staff'] = staff.name
    jail[str(user.id)]['reason'] = reason
    jail[str(user.id)]['roles'] = roles

async def unjail(jail, user, guild):
    for r in jail[str(user.id)]['roles']:
        role = guild.get_role(int(r))
        await user.add_roles(role)
    del jail[str(user.id)]

async def jail_lookup(jail, user):
    if str(user.id) in jail:
        return str(user.id)


def setup(client):
    t = Jail(client)
    client.add_cog(t)