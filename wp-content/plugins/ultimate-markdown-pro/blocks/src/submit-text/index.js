const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'submit-text',
    {
      icon: false,
      render,
    },
);