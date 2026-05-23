/**
 * Tailwind CSS v4 Configuration for Dark Glassmorphism Login Form
 * 
 * This configuration ensures all utilities and custom values work correctly
 * with the LoginForm and ReCaptchaWrapper components.
 */

import type { Config } from 'tailwindcss'

export default {
  content: [
    './index.php',
    './resources/**/*.{vue,js,ts,jsx,tsx}',
    './app/**/*.php',
  ],
  
  theme: {
    extend: {
      /* Color extensions for glassmorphism */
      colors: {
        'glass-dark': 'rgba(255, 255, 255, 0.1)',
        'glass-light': 'rgba(255, 255, 255, 0.05)',
      },
      
      /* Enhanced backdrop blur for glass effect */
      backdropBlur: {
        'xl': '24px',
        '2xl': '32px',
      },
      
      /* Custom backdrop opacity */
      backdropOpacity: {
        5: '0.05',
        10: '0.1',
        15: '0.15',
        20: '0.2',
      },
      
      /* Spacing for form layout */
      spacing: {
        'recaptcha-container': '304px', // reCAPTCHA fixed width
        'form-section': '1.5rem',
      },
      
      /* Border radius for rounded cards */
      borderRadius: {
        'glass': '2.5rem',
      },
      
      /* Custom animations */
      animation: {
        'fade-in': 'fadeIn 0.3s ease-in-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'spin-slow': 'spin 2s linear infinite',
      },
      
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { 
            opacity: '0',
            transform: 'translateY(10px)'
          },
          '100%': { 
            opacity: '1',
            transform: 'translateY(0)'
          },
        },
      },
      
      /* Shadow for depth */
      boxShadow: {
        'glass': '0 8px 32px rgba(0, 0, 0, 0.1)',
        'glass-lg': '0 20px 50px rgba(0, 0, 0, 0.15)',
      },
      
      /* Min/Max heights for form */
      height: {
        'input': '44px', // Mobile touch-friendly
      },
      
      /* Transition timing functions */
      transitionTimingFunction: {
        'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
      },
    },
  },
  
  plugins: [
    /**
     * Custom plugin for glass effect utilities
     * Provides .glass-effect and .glass-effect-sm classes
     */
    function({ addComponents, theme }) {
      addComponents({
        '.glass-effect': {
          '@apply backdrop-blur-xl bg-white/10 border border-white/20': {},
        },
        '.glass-effect-sm': {
          '@apply backdrop-blur-xl bg-white/5 border border-white/10': {},
        },
        '.input-glass': {
          '@apply px-4 py-3 rounded-2xl bg-white/5 border border-white/10 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-white/30 focus:bg-white/10 transition-all duration-200': {},
        },
        '.btn-primary': {
          '@apply px-6 py-3 rounded-2xl bg-white text-slate-950 font-black transition-all duration-200 hover:shadow-lg hover:shadow-white/20 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed': {},
        },
      })
    },
  ],
} satisfies Config
