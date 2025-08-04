const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'export-markdown',
    {
      icon: false,
      render,
    },
);