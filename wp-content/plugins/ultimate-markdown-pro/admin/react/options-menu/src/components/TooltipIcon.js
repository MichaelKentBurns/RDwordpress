import {Tooltip} from 'react-tooltip';
import sanitizeHtml from 'sanitize-html';
import GridiconInfoOutline from 'gridicons/dist/info-outline';
import GridiconExternal from 'gridicons/dist/external';
const { __ } = wp.i18n;

const TooltipIcon = (props) => {

  return (
      <>
        <div className={'tooltip-container'}>
          <div className="tooltip-icon"
               data-tooltip-id={props.name}
          >
              <GridiconInfoOutline size={18} />
          </div>

        </div>
        <Tooltip
            id={props.name}
            place="left"
            openOnClick={true}
            clickable>
            <div>
                {
                    sanitizeHtml(props.text,
                    {
                        allowedTags: [],
                    })
                }
            </div>
            {
                props.tooltipLink
                ?
                <div className="tooltip-link-container">
                    <a href={props.tooltipLink} target={'_blank'}>
                        <span>{__('Learn more', 'ultimate-markdown-pro')}</span>
                        <GridiconExternal size={12} />
                    </a>
                </div>
                :
                ''
            }
          </Tooltip>
      </>
  );
};

export default TooltipIcon;