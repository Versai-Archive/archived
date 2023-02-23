//just something im working on in class because I have some time and this is the only thing i can code in right now :c
//also if you want you can help on this its for seeing a players containers just as a fun side project

/**
command.register('seecontainer', 'see a players E-Chest/inventory', CommandPermmisionLevel.Operator).overload((params, origin, output) => {

   let sender = origin.getEntity() as Player;
  let container = params.container.newResults(origin);

  if (container === "echest" || container === "enderchest" || container === "Ender chest" || container === "E-chest") {
    let sender = origin.getEntity() as Player;

    if (sender == null) {
      return;
    }

    let targets = params.target.newResults(origin);

    if (targets.length === 0) {
      sendMessage(sender, "§cNot enough targets given!!");
    } else if (targets.length > 1) {
      sendMessage(sender, "§To many targets selected!!");
    } else {
      if (targets[0].isPlayer()) {
        //wanna make it like a chest that shows the the players inventory (GUI)
        //packet get enderchest
        //this is so hard to do with nothing infront of me on packets and stuff ;(
        /*
        totally did not just spend like an hour looking for how to get a players E-chest nope...
        ima need help on this one
        ill try to do chest one later

        GET INV
        MAKE CHEST
        FORCE CHEST OPEN TO ORIGIN
        COPY INV PACKET
        PASTE INTO CHEST
        **/

//might work on later when i can work in something other then github
