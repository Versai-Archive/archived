/* eslint-disable no-restricted-imports */
import { command } from 'bdsx/command';
import { CommandPermissionLevel } from '../../../bdsx/bds/command';
import { Player } from '../../../bdsx/bds/player';
import { CxxString } from '../../../bdsx/nativetype';
import * as fs from 'fs';
import { Events as EventsJSON } from '../models/json';
import { join } from 'path';
import { getDirectory1 } from '../database';
import { datas } from '..';

/* KEY
* <> PARAMS
* () FORM
*/

//ps this is not done this is just for me so i can start! :p
export const EVENT_PATH = join(__dirname, "models");

export function updateEvent(title: string, type: string, desc: string): void {
      const eventData: EventsJSON = {
            title: title,
            eventType: type,
            eventDescription: desc,
            creation: Date.now()
      };

      if (!fs.existsSync(EVENT_PATH)) {
          fs.mkdirSync(EVENT_PATH);
      }
      fs.writeFileSync(getDirectory1('events.json'), JSON.stringify(eventData, null, 2));

  }


command.register('setevent', 'set a event data', CommandPermissionLevel.Operator).overload((params, origin, output) => {
      let sender = origin.getEntity() as Player;

      if(params.toggle === 'on') {

      }
}, { toggle: CxxString });
    // /setevent <on | off>




        /* Toggle a JSON or something and make it so that they can use /event */



command.register('event', 'event command').overload((params, origin, output) => {
    // /event <tp | info>
      let sender = origin.getEntity() as Player;

      if(params.info === 'info') {

      }
}, {});

command.register('eventinfo', 'set the info of a event', CommandPermissionLevel.Operator).overload((params, origin, output) => {
      // /eventinfo <title> <event type> <decription> (or maybe it can be a form)
      let sender = origin.getEntity() as Player;

      updateEvent(params.title, params.type, params.desc);

      datas.set()
}, {   title: CxxString,
       type: CxxString,
       desc: CxxString, });


command.register('eventannounce', 'announce the event', CommandPermissionLevel.Operator).overload((params, origin, output) => {
      // /eventannounce (confirm)
      }, {});
