import { useSession } from "next-auth/client";
import { useState, useEffect } from "react";
import styles from "/components/staff.module.css";
import CustomHeader from "../components/header";
import redirect from "nextjs-redirect";
import Link from "next/link";

export default function Dashboard() {
  const [session, loading] = useSession();
  const [content, setContent] = useState();
  const Redirect = redirect("/staff");

  useEffect(() => {
    const fetchData = async () => {
      const res = await fetch("/api/dashboard");
      const json = await res.json();

      if (json.content) {
        setContent(json.content);
      }
    };
    fetchData();
  }, [session]);

  if (typeof window !== "undefined" && loading) return null;
  if (!session || !session.staff) {
    return <Redirect>no</Redirect>;
  }
  return (
    <div className={styles.background}>
      <CustomHeader />
      <div className="absolute top-1/3 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
        <img
          src={session.user.image}
          className="mx-auto rounded-full border-or border-2 border-solid p-2"
        ></img>
        <h1 className="mt-7 font-Muli text-white text-2xl">
          Signed in as {session.user.name}
        </h1>
        <br></br>
        <button>
          <Link href="/dashboard">
            <a className="font-Muli text-or text-xl">To the Dashboard</a>
          </Link>
        </button>
        <br></br>
      </div>
    </div>
  );
}
