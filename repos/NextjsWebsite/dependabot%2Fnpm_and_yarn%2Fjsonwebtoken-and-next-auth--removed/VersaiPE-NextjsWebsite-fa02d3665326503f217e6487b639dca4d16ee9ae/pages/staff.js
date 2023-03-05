import CustomHeader from "../components/header";
import React from "react";
import Link from "next/link";
import styles from "/components/staff.module.css";
import { signIn, signOut, useSession } from "next-auth/client";
import CustomFooter from "../components/footer";
import libquery from "libquery";
let players;

export default function Home({ play }) {
  const [session, loading] = useSession();
  let players;

  return (
    <div className={styles.background}>
      <CustomHeader />
      {!session && (
        <>
          <div className="h-full">
            <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
              <h1 className="text-red-500 font-Muli">Not Signed In</h1>
              <br />
              <button
                onClick={() => signIn("discord")}
                className={styles.bluebutton}
              >
                Sign In With Discord
              </button>
            </div>
          </div>
        </>
      )}
      {session && (
        <div className="h-full overflow-auto">
          <div className="mt-20 mx-auto w-60">
            <img
              src={session.user.image}
              className="mx-auto rounded-full border-or border-2 border-solid p-2"
            ></img>
            <h1 className="mt-7 font-Muli text-white text-2xl">
              Signed in as {session.user.name}
            </h1>
            <br></br>
            <div className="w-60">
              <button className="border-4 border-red-500 text-red-500 p-4 rounded-lg hinge block w-full font-Muli">
                Search for a punishment
              </button>
              <button className="border-4 border-green-500 text-green-500 p-4 rounded-lg hinge block mt-5 w-full font-Muli">
                New punishment
              </button>
              <button className="border-4 border-yellow-600 text-yellow-600 p-4 rounded-lg hinge block mt-5 w-full font-Muli">
                Request punishment
              </button>
              <button className="border-4 border-blue-400 text-blue-400 p-4 rounded-lg hinge block mt-5 w-full font-Muli">
                Online Time & Last Seen
              </button>
            </div>
          </div>
          <h1 className="font-Muli text-white mt-10 mx-auto">
            Current Staff Online
          </h1>
          <p className="text-white font-Muli w-11/12 text-center h-auto mx-auto">
            {play}
          </p>
          <button
            className="font-Muli text-red-400 mt-10 mb-10"
            onClick={signOut}
          >
            Sign out
          </button>
        </div>
      )}
      <CustomFooter />
    </div>
  );
}

export async function getServerSideProps() {
  await libquery
    .query("versai.pro", 19132)
    .then((data) => {
      let a = [];
      data.players = data.players.filter(
        (username) => username == "ShushImSam"
      );
      data.players.forEach((p) => a.push(" " + p));
      console.log(a);

      if (a.length === 0) {
        a = "None";
      }
      return (players = a.toString());
    })
    .catch((err) => {
      return (players = "");
    });
  return {
    props: { play: players },
  };
}
