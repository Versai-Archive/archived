import CustomHeader from "../components/header";
import styles from "/components/index.module.css";
import libquery from "libquery";
import CustomFooter from "../components/footer";
let players = 0;

export default function Home({ play }) {
  return (
    <div className="w-full h-full" id={styles.bg}>
      <CustomHeader />
      <div className="block text-center bg-black p-10 bg-opacity-50 phone:mt-0 h-full small:h-auto">
        <img
          src="/images/versai.svg"
          className="mx-auto animate__animated animate__pulse animate__infinite animate__slower mb-10 desktop:mt-48 fil  tablet:mt-32 small:w-96"
          width="600px"
        ></img>
        <div className="mb-14 small:mb-12">
          <img src="/images/ripple.png" className="inline" width="50px"></img>
          <h2 className="text-white font-Muli inline">
            There is currently {play} players on Versai
          </h2>
        </div>

        <ul className="inline-block">
          <li className="inline-block tablet:block mx-14 bouncy small:mb-10">
            <img
              src="/images/cube.png"
              width="150px"
              className="small:w-24 small:mx-auto"
            ></img>
            <h3 className="text-4xl text-white font-mono mt-5 small:text-2xl">
              Discord
            </h3>
          </li>
          <li className="inline-block tablet:block mx-14 small:my-10 bouncy">
            <img
              src="/images/chest.png"
              width="150px"
              className="small:w-24 small:mx-auto"
            ></img>
            <h3 className="text-4xl text-white font-mono mt-5 small:text-2xl">
              Store
            </h3>
          </li>
          <li className="inline-block tablet:block mx-14 small:my-10 bouncy">
            <img
              src="/images/dirt.png"
              width="150px"
              className="small:w-24 small:mx-auto"
            ></img>
            <h3 className="text-4xl text-white font-mono mt-5 small:text-2xl">
              Vote
            </h3>
          </li>
        </ul>
      </div>
      <CustomFooter />
    </div>
  );
}

export async function getServerSideProps() {
  await libquery
    .query("versai.pro", 19132)
    .then((data) => {
      return (players = data.online);
    })
    .catch((err) => {
      return (players = 0);
    });
  return {
    props: { play: players },
  };
}
