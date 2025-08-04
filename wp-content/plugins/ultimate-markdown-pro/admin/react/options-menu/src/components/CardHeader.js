const { __ } = wp.i18n;

const CardHeader = (props) => {
  return (
      <div className={'settings-card-header'}>
        <div className={'settings-card-label'}>{props.card.title}</div>
        <div className={'settings-card-action'}>
          <button
              className={'settings-card-save-button'}
              onClick={props.handleSave}
              name={props.index + '-save'}
              cardid={props.index}
          >{__('Save settings', 'ultimate-markdown-pro')}
          </button>
        </div>
      </div>
  );
};

export default CardHeader;