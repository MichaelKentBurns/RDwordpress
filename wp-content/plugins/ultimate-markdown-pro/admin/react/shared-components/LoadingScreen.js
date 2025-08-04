const LoadingScreen = (props) => {
  return (
      <div className={'loading-screen'}>
          <div className={'loading-screen__wrapper'}>
              <div className={'loading-screen__content'}>
                  <div className="loading-screen__spinner-container">
                      <div className="loading-screen__spinner"></div>
                  </div>
                  <div className="loading-screen__text">{typeof props.dataUpdateRequired !== 'undefined' && props.dataUpdateRequired ? props.generatingDataMessage : props.loadingDataMessage}</div>
              </div>
          </div>
      </div>
  );
};

export default LoadingScreen;