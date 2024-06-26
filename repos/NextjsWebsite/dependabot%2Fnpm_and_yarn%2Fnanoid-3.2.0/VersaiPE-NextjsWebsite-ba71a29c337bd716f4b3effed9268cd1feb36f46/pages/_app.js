import "../styles/globals.css";
import "tailwindcss/tailwind.css";
import "animate.css";
import { Provider } from "next-auth/client";
function MyApp({ Component, pageProps }) {
  return (
    <Provider session={pageProps.session}>
      <Component {...pageProps} />
    </Provider>
  );
}

export default MyApp;
