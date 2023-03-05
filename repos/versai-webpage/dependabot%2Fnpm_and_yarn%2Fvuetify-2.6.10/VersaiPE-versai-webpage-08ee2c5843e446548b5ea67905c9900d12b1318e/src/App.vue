<template>
  <v-app id="inspire">
    <v-navigation-drawer
      v-model="drawer"
      app
    >
      <div class="menu">
  <li class="menu-item" id="profile">
    <a href="#profile" class="btn">
      <i class="far fa-user"></i>
      Server
    </a>
    <div class="menu-item__sub">
      <a href="players-search.html">Info</a>
      <a href="#">Servers</a>
    </div>
  </li>
  <li class="menu-item" id="messages">
    <a href="#messages" class="btn">
      <i class="far fa-envelope"></i>
      Players
    </a>
    <div class="menu-item__sub">
      <a href="#">Search</a>
      <a href="#">Staff</a>
      <a href="#">Voters</a>
    </div>
  </li>
  <li class="menu-item" id="settings">
    <a href="#settings" class="btn">
      <i class="fas fa-cog"></i>
      Settings
    </a>
    <div class="menu-item__sub">
      <a href="#">Password</a>
      <a href="#">Language</a>
    </div>
  </li>
  <li class="menu-item">
    <a href="#" class="btn">
      <i class="fas fa-sign-out-alt"></i>
      Logout
    </a>
  </li>
</div>
    </v-navigation-drawer>

    <v-app-bar app>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

      <v-toolbar-title>versai.pro - <small>19132</small></v-toolbar-title>
    </v-app-bar>

    <v-main>
      
    </v-main>
  </v-app>
</template>

<script>
  export default {
    data: () => ({ drawer: null }),
  }
</script>

<style lang="scss">
  
  $purple: #9f02e8;
  $dark-purple: #500075;
  $background: #081118;
  $grey: #333;
  $light-grey: #3b3b3b;


* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: sans-serif;
}

body {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: $background;
}

.menu {
  width: 20rem;
  border-radius: 0.2rem;
  overflow: hidden;
  color: #EEEEEE;
}

.menu a {
  text-decoration: none;
  color: #eeeeee;
}

.menu-item {
  list-style: none;
  border-top: 1px solid $dark-purple;
  overflow: hidden;
}

.btn {
  display: block;
  padding: 1rem 1.2rem;
  background: $purple;
  color: white;
  position: relative;
}
.btn::before {
  content: '';
  position: absolute;
  width: 1rem;
  height: 1rem;
  background: $purple;
  left: 1.5rem;
  bottom: -0.5rem;
  transform: rotate(45deg);
}
.btn i {
  margin-right: 1rem;
}

.menu-item__sub {
  background: $grey;
  overflow: hidden;
  transition: max-height 0.3s;
  max-height: 0;
}
.menu-item__sub a {
  display: block;
  padding: 1rem 1.6rem;
  color: white;
  font-size: 0.9rem;
  position: relative;
  border-bottom: 1px solid $light-grey;
}
.menu-item__sub a::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 0.4rem;
  height: 100%;
  background: $purple;
  transform: translatex(-0.4rem);
  transition: 0.3s;
  opacity: 0;
}
.menu-item__sub a:hover::before {
  opacity: 1;
  transform: translatex(0);
}

.menu-item:target .menu-item__sub {
  max-height: 10em;
}
</style>