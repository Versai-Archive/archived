module.exports = {
  purge: ["./pages/**/*.{js,ts,jsx,tsx}", "./components/**/*.{js,ts,jsx,tsx}"],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      fontFamily: {
        poppin: ["Poppins", "sans-serif"],
        kiwi: ["Kiwi Maru"],
        roboto: ["Roboto"],
        Muli: ["Mulish"],
      },
      screens: {
        desktop: { min: "780px" },
        tablet: { min: "500px", max: "780px" },
        phone: { min: "0px", max: "500px" },
        small: { max: "780px" },
      },
    },
  },
  variants: {
    extend: {},
  },
  plugins: [],
};
