export function nReady(target:NetworkIdentifier) {
  let player = DataById(target)[0];
  let sch_ = dataJs.find((v) => v.Name == playerName);
    if (sch_ != undefined) {
      let sch = dataJs.find((v) => v.gangId == sch_.gangId);
      if (sch == undefined) {
        let targetj = dataJs.find((e:any) => e.Name == playerName);
        if (targetj == undefined) return;
        
        let state = dataJs.indexOf(targetj);
        datsJs.splice(state, 1);
        sendMessage(target, '§l§cThere was a problem with guild data';
      }
    
    }
  let gjs = dataJs.map((e:any) => e.Name);
  if gjs.includes(`$${playerName}` == true) {
    let data = dataJs.filter((e:any) => e:Name == `$${playerName}`)[0];
    if (data.perm == 'break') {
      formSend(target, {
        type: "custom_form",
        title: "Guild",
        content: [
        {
          "type": "label",
          "text": "§l§cGuild has been disbanded!"
        }
       
       ]
      }, () => {
        let state = dataJs.indexOf(data);
        dataJs.splice(state, 1);
        nMain(target);
      });
    } else if (data.perm == 'kicked') {
      formSend(target, {
        type: "custom_form",
        title: "guild",
        content: [
          {
            "type": "label",
            "text": "§l§cYou have been kicked from your guild!",
            
          }]}, () => {
            let state = dataJs.indexOf(data);
            dataJs.splice(state, 1);
            Nmain(target);
          });
      }
  
  } else if (gjs.includes(playerName) == false) {
    nMain(target)
  } else {
    let data = dataJs.filter((e:any) => e.Name == playerName)[0];
        if (data.perm == 'leader') {
            nMain2L(target);
        }
        if (data.perm == 'coleader') {
            nMain2C(target);
        }
        if (data.perm == 'member') {
            nMain2M(target);
        }
  };
    
}
nethook.after(PacketId.Login).on((ptr, networkIdentifier)=>{
    const cert = ptr.connreq.cert;
    const xuid = cert.getXuid();
    const username = cert.getId();
    let gjs = dataJs.map((e:any) => e.xuid);
    if (gjs.includes(xuid)) {
        let data = dataJs.find((e:any) => e.xuid == xuid);
        let state = dataJs.indexOf(data);
        if (data.guildID != "") data.Name = username;
        if (data.guildID == "") data.Name = "$" + username;
        dataJs.splice(state, 1, data);
    }
});

export function nMain(target:NetworkIdentifier) {
  formSend(target, {
    type: "form",
    title: "Guild",
    content: '',
    buttons: [
    {
       text: "§6Gang Ranking"
    },
    {
       text: "§9Find a Gang"   
    },
    {
       text: "§aMake a Guild"
    },
    {
       text: "§cInvitations"
    }
    ]
  }, () => {
    if (data == 0) rank(target);
    if (data == 1) gSearch(target);
    if (data == 2) gCreate(target);
    if (data == 3) gInvlist(target);
  });
});

export function nMain2(target:NetworkIdentifier) { //player
  let data = dataJs.filter((e:any) => e.Name == dataById(target[0])[0];
  let guild = guildJs.filter((e:any) => e.guildId == data.guildId)[0];
  let id = String(data.guildId);
    formSend(target, {
      type: "form",
      title: "Guild",
      content: `§l§a
      ${gang.name} §7- §6Level §7-§6 
      ${gang.level} \n
      §c${gang.subtitle} \n
      §7(§2 ${gang.xp} §7/§2 ${gang.xpM} §7) - §2Until next level \n
      §3My rank §7-§3 ${gang.perm}`   
      buttons: [
      {
        "text": "Gang Rank"
      },                              //LEFT OFF HERE
      {
        "text": ""
      },
      {
        "text": ""
      }
     ]
    }
}

export function nMain2C(target:NetworkIdentifier) { //coLeader
}

export function nMain2L(target:NetworkIdentifier) { //leader
}

export function leaderCmd(target:NetworkIdentifier, id:any) {
}

export function rank(target:NetworkIdentifier, num:number) {
}

export function gangBreak(target:NetworkIdentifier, id:any) { //omfg i misread that even after i typed it LOL :skull:
}

export function gLead(target:NetworkIdentifier, id:any) {
}

export function gCo(target: NetworkIdentifier, id: any) {
}

export function gKick(target:NetworkIdentifier, id:any) {
}

export function gInvite(target:NetworkIdentifier, id:any) {
}

export function gInvList(target:NetworkIdentifier) {
}

export function gOut(target:NetworkIdentifier, id:any) {
}

export function gSubTitle(target:NetworkIdentifier, id:any) {
}

export function gDescChange(target:NetworkIdentifier, id:any) {
}

export function gMemList(target:NetworkIdentifier, id:any) {
}

export function gSearch(target:NetworkIdentifier) {
}

export function searchRs(target:NetworkIdentifier, input: string) {
}

export function regExp(str: string) {
}

export function gCreate(target: NetworkIdentifier) {
}

export function addExp(xp:number, id:string, IdJs:any) {
}

export function backup() {
}

/**
  *get playerXuid by Name
*/
export function xuidByName(PlayerName: string) {
    let Rlt:any = nXt.get(PlayerName);
    return Rlt;
}
/**
  *get playerName by Id
*/
export function nameById(networkIdentifier: NetworkIdentifier) {
    let actor = networkIdentifier.getActor();
    let playerName:string;
    try {
        let entity = actor!.getEntity();
        playerName = system.getComponent(entity, "minecraft:nameable")!.data.name;
    } catch {
        playerName = nMt.get(networkIdentifier);
    }
    return playerName;
}
/**
  *get playerData by Id
  *result = [name,actor,entity, xuid]
*/
export function dataById(networkIdentifier: NetworkIdentifier) {
    let actor = networkIdentifier.getActor();
    let entity = actor!.getEntity();
    let name = actor!.getName();
    let xuid:any = nXt.get(name);
    return [name, actor, entity, xuid];
}
/**
  *get playerId by Name
*/
export function idByName(PlayerName: string) {
    let Rlt:NetworkIdentifier = nIt.get(PlayerName);
    return Rlt;
}

/**
  *send a form to a player
   *from mini as well
*/

export function formSend(target: NetworkIdentifier, form: formJSONTYPE|object, handler?: (data: any) => void, id?:number) {
    try {
        const modalPacket = ShowModalFormPacket.create();
        let formId = Math.floor(Math.random() * 1147483647) + 1000000000;
        if (typeof id === "number") formId = id;
        modalPacket.setUint32(formId, 0x30);
        modalPacket.setCxxString(JSON.stringify(form), 0x38);
        modalPacket.sendTo(target, 0);
        if (handler === undefined) handler = ()=>{}
        if (!FormData.has(target)) {
            FormData.set(target, [
                {
                    Id: formId,
                    func: handler
                }
            ])
        } else {
            let f = FormData.get(target)!;
            f.push({
                Id: formId,
                func: handler
            })
            FormData.set(target, f);
        }
        modalPacket.dispose();
    } catch (err) {}
}
nethook.raw(PacketId.ModalFormResponse).on((ptr, size, target) => {
    ptr.move(1);
    let formId = ptr.readVarUint();
    let formData = ptr.readVarString();
    let dataValue = FormData.get(target)!.find((v)=> v.Id === formId)!;
    let data = JSON.parse(formData.replace("\n",""));
    if (dataValue === undefined) return;
    dataValue.func(data);
    let f = FormData.get(target)!;
    f.splice(f.indexOf(dataValue), 1);
    FormData.set(target, f);
});
