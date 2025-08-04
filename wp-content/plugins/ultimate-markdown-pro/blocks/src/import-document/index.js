const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'daextulmap-import-document',
    {
      icon: false,
      render,
    },
);