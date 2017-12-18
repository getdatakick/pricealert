// @flow
import type { Settings } from 'types';

export const validateEmail = (email: ?string): boolean => {
  if (email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  }
  return false;
};

export const validateSlider = (slider: number, settings: Settings): boolean => {
  const minDiscount = settings.config.minDiscount || 0;
  return slider > 0 && slider < 1 && slider >= minDiscount;
};
