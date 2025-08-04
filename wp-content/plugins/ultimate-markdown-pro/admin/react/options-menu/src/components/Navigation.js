import React from 'react';
import GridiconSearch from 'gridicons/dist/search';
import GridiconCross from 'gridicons/dist/cross';

const { __ } = wp.i18n;
import settings from '../../../shared-components/data/settings';

const Navigation = (props) => {
  return (
      <div className={'settings-navigation'}>

        <div className={`nav-tabs ${props.isSearching === true &&
        'display-hidden'}`}>

        {
          settings.pages.map((page, index) => {
            return (
                <div
                    className={`single-tab ${props.activePage === (index+1)
                        ? 'active-tab'
                        : ''}`}
                    name={'tab' + (index+1)}
                    onClick={(event) => props.onTabClick(event)}
                    data-tab-id={index+1}
                    key={index}
                >{page.title}
                </div>
            );
          })
        }

        </div>

        <div
            className="search-icon-container"
            onClick={(event) => props.onSearchIconClick(event)}
        >
            <GridiconSearch size={24} />
        </div>

        <input
            id={'search-input'}
            className={`search-input ${props.isSearching === false &&
            'display-hidden'}`}
            value={props.searchInput}
            onChange={(event) => props.onHandleSearchInputChanges(event)}
            name={'search_input'}
            placeholder={__('Search for a feature.', 'ultimate-markdown-pro')}
        />

        <div
        className={`close-search-icon-container ${props.isSearching === false &&
        'display-hidden'}`}
        onClick={(event) => props.onCloseSearchIconClick(event)}
        >
          <GridiconCross size={24} />
        </div>

      </div>
  );
};

export default Navigation;