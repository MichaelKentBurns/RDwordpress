import GridiconCheckmark from "gridicons/dist/checkmark";
import GridiconCross from "gridicons/dist/cross";

const { __ } = wp.i18n;

const DismissibleNotice = () => {
  return (
      <div id={'notification-message'}>
        <div className="notification-message-wrapper">
          <div className="notification-message-icon">
            <GridiconCheckmark size={24} />
          </div>
          <div className="notification-message-content">{ __('Updated settings.', 'ultimate-markdown-pro') }</div>
          <div className="notification-message-dismiss-icon">
            <GridiconCross size={24} />
          </div>
        </div>
      </div>
  );
};

export default DismissibleNotice;