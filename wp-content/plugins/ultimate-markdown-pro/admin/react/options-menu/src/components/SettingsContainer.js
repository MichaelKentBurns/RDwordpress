import CardHeader from './CardHeader';
import CardBody from './CardBody';
import settings from '../../../shared-components/data/settings';

const SettingsContainer = (props) => {

  /**
   * Return the 'active-page' class only if the active page selected with the
   * tabs in the top of the page is the same as the index of the page and the
   * search input is empty.
   *
   * @returns {string}
   */
  function pageIsActive(index) {

    if (props.activePage === (index + 1) ||
        (props.isSearching === true && props.searchInput !== '')) {
      return 'active-page';
    } else {
      return '';
    }

  }

  /**
   * Return the 'active-page-title' class only if the searched keyword
   * props.searchInput is found in at least one of the cards of the page.
   *
   * @returns {string}
   */
  function pageDescriptionIsActive(page) {

    let activeCards = 0;

    page.cards.map((card, index) => {
      if(cardIsActive(card) !== ''){
        activeCards++;
      }
    });

    if (activeCards > 0) {
      return 'active-page-description';
    } else {
      return '';
    }

  }

  /**
   * Return the 'active-card' class only if the searched keyword
   * prop.searchInput is found in the card title or in the labels of the fields.
   *
   * @param card
   * @returns {string}
   */
  function cardIsActive(card) {

    //generate the keywords from the card title and the labels of the fields
    let keywords = [];
    keywords.push(card.title.toLowerCase());
    card.options.map((option, index) => {
      keywords.push(option.label.toLowerCase());
    });

    if ((props.isSearching === false ||
            props.searchInput === '') ||
        (props.isSearching === true &&
            props.searchInput !== '' &&
            !keywords.every((keyword) => keyword.indexOf(props.searchInput.toLowerCase()) < 0)
        )) {
      return 'active-card';
    } else {
      return '';
    }

  }

  return (

      <div className={'settings-container'}>

      {settings.pages.map((page, index) => {

          return (

              <div key={index}
                   className={'settings-page-container ' + pageIsActive(index)}>

                <div className={'settings-page-description' + pageDescriptionIsActive(page)}>
                  <h2>{ page.description }</h2>
                </div>

                {page.cards.map((card, index) => {

                  return (

                      <div key={(index + 1)} className={'settings-card ' + cardIsActive(card)}>

                        <CardHeader
                            handleSave={props.handleSave}
                            index={(index + 1)}
                            card={card}
                        />

                        <CardBody
                            handleChanges={props.handleChanges}
                            handleToggleChanges={props.handleToggleChanges}
                            handleReactSelectChanges={props.handleReactSelectChanges}
                            handleReactSelectMultipleChanges={props.handleReactSelectMultipleChanges}
                            handleReactColorfulChanges={props.handleReactColorfulChanges}
                            handleRangeControlChanges={props.handleRangeControlChanges}
                            formData={props.formData}
                            card={card}
                        />

                      </div>

                  );

                })}

              </div>

          );

        })}

        </div>

  );
};

export default SettingsContainer;