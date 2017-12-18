// @flow

import type { Settings } from 'types';
import injectTapEventPlugin from 'react-tap-event-plugin';
import React from 'react';
import reducer from 'reducer';
import { createStore, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import { render } from 'react-dom';
import { setShow, setWidth, init, urlChanged } from 'actions';
import { readEmail } from 'utils/storage';
import Theme from 'components/theme/theme';
import createMiddleware from 'middleware';
import logger from 'redux-logger';

const development = process.env.NODE_ENV !== 'production';

const getWindowWidth = () => window.innerWidth;
const getHash = () => window.location.hash.replace('#', '');
const getEmail = (settings) => readEmail() || settings.customer.email || '';

const main = () => {
  injectTapEventPlugin();
  const target = document.getElementById('pricealert-dialog');
  const settings: Settings = window.priceAlertData;
  if (settings && target) {
    const middlewares = [ createMiddleware(settings) ];
    if (development) {
      middlewares.push(logger);
    }
    const store = createStore(reducer, applyMiddleware(...middlewares));
    store.dispatch(init(settings, getHash(), getWindowWidth(), getEmail(settings)));
    window.addEventListener('resize', () => store.dispatch(setWidth(getWindowWidth())));
    window.addEventListener('hashchange', () => store.dispatch(urlChanged(settings, getHash())));
    window.PriceAlert = (show: boolean) => store.dispatch(setShow(show));
    if (window.showPriceAlert) {
      window.PriceAlert(true);
    }
    render((
      <Provider store={store}>
        <Theme settings={settings} />
      </Provider>
    ), target);
  }
};

if (document.readyState === "complete") {
  main();
} else {
  window.addEventListener('load', main);
}
