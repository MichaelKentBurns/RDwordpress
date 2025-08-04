const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'daextulmap-load-document',
    {
      icon: false,
      render,
    },
);