/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.{php,html,js}",
    "./src/Views/**/*.{php,html}",
    "./node_modules/flowbite/**/*.js"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',
        card: 'hsl(var(--card))',
        'card-foreground': 'hsl(var(--card-foreground))',
        popover: 'hsl(var(--popover))',
        'popover-foreground': 'hsl(var(--popover-foreground))',
        primary: {
          DEFAULT: '#1e3a5f',
          50: '#f0f5fa',
          100: '#e3ecf5',
          200: '#cadbed',
          300: '#a3c0de',
          400: '#769fca',
          500: '#5782b5',
          600: '#4468a0',
          700: '#385382',
          800: '#1e3a5f',
          900: '#2d3a50',
          950: '#1d2433',
          foreground: '#ffffff',
        },
        secondary: {
          DEFAULT: '#d4af37',
          50: '#fefde8',
          100: '#fffcc1',
          200: '#fff586',
          300: '#ffe841',
          400: '#ffd70d',
          500: '#d4af37',
          600: '#ca9216',
          700: '#a16816',
          800: '#85511c',
          900: '#71431e',
          950: '#42230f',
          foreground: '#2c2825',
        },
        accent: {
          DEFAULT: '#8b1538',
          50: '#fef2f3',
          100: '#fde6e8',
          200: '#fbd0d5',
          300: '#f7aab2',
          400: '#f17a89',
          500: '#e74c60',
          600: '#d02e47',
          700: '#8b1538',
          800: '#7a1530',
          900: '#6a142b',
          950: '#3a0a16',
          foreground: '#ffffff',
        },
        roman: {
          marble: '#faf9f7',
          gold: '#d4af37',
          wine: '#8b1538',
          navy: '#1e3a5f',
          bronze: '#6b4423',
          stone: '#a8a5a0',
        },
        perfil: {
          'lider-opinion': '#8b5cf6',
          'influencer': '#ec4899',
          'militante': '#ef4444',
          'simpatizante': '#f59e0b',
          'neutral': '#6b7280',
          'opositor': '#dc2626',
          'academico': '#3b82f6',
          'empresarial': '#10b981',
          'sindical': '#f59e0b',
          'religioso': '#8b5cf6',
          'comunitario': '#06b6d4',
          'otro': '#64748b',
        },
        estado: {
          'nuevo': '#3b82f6',
          'crecio': '#10b981',
          'igual': '#f59e0b',
          'decrece': '#f97316',
          'desvinculado': '#ef4444',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
        display: ['Poppins', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.3s ease-in-out',
        'slide-in': 'slideIn 0.3s ease-in-out',
        'bounce-subtle': 'bounceSubtle 2s infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideIn: {
          '0%': { transform: 'translateY(-10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        bounceSubtle: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-5px)' },
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class',
    }),
    require('@tailwindcss/typography'),
    require('daisyui'),
    require('flowbite/plugin'),
  ],
  daisyui: {
    themes: [
      {
        light: {
          ...require("daisyui/src/theming/themes")["light"],
          primary: "#1e3a5f", // Roman Navy
          secondary: "#d4af37", // Roman Gold
          accent: "#8b1538", // Roman Wine
          neutral: "#2c2825", // Dark brown
          "base-100": "#faf9f7", // Roman Marble
          "base-200": "#f5f4f2",
          "base-300": "#a8a5a0", // Roman Stone
          info: "#1e3a5f",
          success: "#10b981",
          warning: "#d4af37",
          error: "#dc2626",
        },
        dark: {
          ...require("daisyui/src/theming/themes")["dark"],
          primary: "#1e3a5f", // Roman Navy
          secondary: "#4a5568",
          accent: "#d4af37", // Roman Gold
          neutral: "#2c2825",
          "base-100": "#1a1917",
          "base-200": "#2c2825",
          "base-300": "#3a3530",
          info: "#5782b5",
          success: "#10b981",
          warning: "#d4af37",
          error: "#ef4444",
        },
      },
    ],
    darkTheme: "dark",
    base: true,
    styled: true,
    utils: true,
    prefix: "",
    logs: true,
    themeRoot: ":root",
  },
}
