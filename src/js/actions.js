// @flow
import type { Step, Settings } from 'types';

export const Types = {
  init: 'INIT',
  urlChanged: 'URL_CHANGED',
  setWidth: 'SET_WIDTH',
  setShow: 'SET_SHOW',
  setSnackbar: 'SET_SNACKBAR',
  setAttribute: 'SET_ATTRIBUTE',
  setEmail: 'SET_EMAIL',
  setSlider: 'SET_SLIDER',
  setStep: 'SET_STEP',
  submit: 'SUBMIT'
};

type InitAction = {
  type: 'INIT',
  settings: Settings,
  url: string,
  width: number,
  email: string
}

type UrlChangedAction = {
  type: 'URL_CHANGED',
  settings: Settings,
  url: string
}

type SubmitAction = {
  type: 'SUBMIT',
  data: {}
}

type SetWidthAction = {
  type: 'SET_WIDTH',
  width: number
}

type ShowAction = {
  type: 'SET_SHOW',
  show: boolean
}

type SetSnackbarAction = {
  type: 'SET_SNACKBAR',
  message: ?string
}

type SetEmailAction = {
  type: 'SET_EMAIL',
  email: string
}

type SetStepAction = {
  type: 'SET_STEP',
  step: Step
}

type SetSliderAction = {
  type: 'SET_SLIDER',
  slider: number
}

type SetAttributeAction = {
  type: 'SET_ATTRIBUTE',
  attributeId: string,
  value: string
}

export type Action = (
  InitAction |
  UrlChangedAction |
  SetWidthAction |
  ShowAction |
  SetStepAction |
  SetSnackbarAction |
  SetSliderAction |
  SetAttributeAction |
  SetEmailAction |
  SubmitAction
);

export const init = (settings: Settings, url: string, width: number, email: string):InitAction => ({ type: Types.init, settings, url, width, email });
export const urlChanged = (settings: Settings, url: string):UrlChangedAction => ({ type: Types.urlChanged, settings, url });
export const setShow = (show: boolean):ShowAction => ({ type: Types.setShow, show });
export const setWidth = (width: number):SetWidthAction => ({ type: Types.setWidth, width });
export const setSnackbar = (message: ?string):SetSnackbarAction => ({ type: Types.setSnackbar, message });
export const setEmail = (email: string):SetEmailAction => ({ type: Types.setEmail, email });
export const setStep = (step: Step):SetStepAction => ({ type: Types.setStep, step });
export const setSlider = (slider: number):SetSliderAction => ({ type: Types.setSlider, slider });
export const setAttribute = (attributeId: string, value:string):SetAttributeAction => ({ type: Types.setAttribute, attributeId, value });
export const submit = (data: {}):SubmitAction => ({ type: Types.submit, data });
