// @flow

import { assoc, clamp } from 'ramda';
import type { Settings, Attributes, State, Step } from 'types';
import type { Action } from 'actions';
import { Types } from 'actions';
import { getAttributes } from 'utils/url';

const show = (state: State, show: boolean): State => ({ ...state, show, step: show ? 'product': state.step });
const setSlider = (state: State, slider: number): State => ({ ...state, slider });
const setWidth = (state: State, width: number): State => ({ ...state, width });
const setEmail = (state: State, email: string): State => ({ ...state, email });
const setAgree = (state: State, agree: boolean): State => ({ ...state, agree });
const setStep = (state: State, step: Step): State => ({ ...state, step });
const setSnackbarMessage = (state: State, message: ?string): State => ({ ...state, message });
const setAttribute = (state:State, id: string, value: string): State => {
  const { attributes, ...rest } = state;
  return {
    ...rest,
    attributes: assoc(id, value, attributes)
  };
};
const urlChanged = (state: State, attributes: Attributes, url: string): State => ({
  ...state,
  attributes: getAttributes(url, attributes),
});
const initialState = (attributes: Attributes, defaultDiscount: number, url: string, width: number, email: string, agree: boolean): State => ({
  show: false,
  message: null,
  email,
  agree,
  step: 'product',
  slider: defaultDiscount,
  attributes: getAttributes(url, attributes),
  width
});
const getInitialState = (settings: Settings, url: string, width: number, email: string): State => {
  const { product, config } = settings;
  const { defaultDiscount, minDiscount } = config;
  const value = clamp(minDiscount || 0, 1, defaultDiscount || 0.8);
  return initialState(product.attributes, value, url, width, email, !settings.config.consent);
};

export default (prevState: ?State, action: Action): State => {
  const state = prevState || initialState([], 0, '', 1024, '', false);
  switch (action.type) {
    case Types.init:
      return getInitialState(action.settings, action.url, action.width, action.email);
    case Types.urlChanged:
      return urlChanged(state, action.settings.product.attributes, action.url);
    case Types.setShow:
      return show(state, action.show);
    case Types.submit:
      return show(state, false);
    case Types.setStep:
      return setStep(state, action.step);
    case Types.setSnackbar:
      return setSnackbarMessage(state, action.message);
    case Types.setSlider:
      return setSlider(state, action.slider);
    case Types.setAttribute:
      return setAttribute(state, action.attributeId, action.value);
    case Types.setEmail:
      return setEmail(state, action.email);
    case Types.setWidth:
      return setWidth(state, action.width);
    case Types.agreeGDPR:
      return setAgree(state, action.agree);
  }
  return state;
};
