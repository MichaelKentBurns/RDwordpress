import settings from '../../../shared-components/data/settings';

const MainHeader = () => {
  return (
      <div className={'main-header'}>
        <h1>{settings.pluginName}</h1>
      </div>
  );
};

export default MainHeader;