module.exports = {
  formatDate: function (date) {
    let Months = {
      0: "Jan",
      1: "Feb",
      2: "Mar",
      3: "Apr",
      4: "May",
      5: "Jun",
      6: "Jul",
      7: "Aug",
      8: "Sep",
      9: "Oct",
      10: "Nov",
      11: "Dec",
    };
    return `${Months[date.getMonth()]}, ${date.getDate()}, ${date.getFullYear()}`;
  },
  cleanPerms: function (permissions) {
    let permission = "";
    if (permissions.has("ADMINISTRATOR"))
      return (permission = "All Permissions");
    else if (permissions.has("MANAGE_GUILD")) permission = "Manage Guild";
    else if (permissions.has("BAN_MEMBERS")) permission = "Ban Members";
    else if (permissions.has("KICK_MEMBERS")) permission = "Kick Members";
    else if (permissions.has("MANAGE_MESSAGES")) permission = "Manage Messages";
    else permission = "Basic Permissions";
    return permission;
  },
};
