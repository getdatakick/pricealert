// @flow
import type { Currency } from 'types';

export const roundCurrency = (price: number): string => {
  if (window.ps_round) {
    return window.ps_round(price.toFixed(10), 2);
  }
  return price.toFixed(2);
};

export const formatCurrency = (price: number, currency: Currency): string => {
  const rounded = roundCurrency(price);
  if (window.formatCurrency) {
    return window.formatCurrency(rounded, currency.format, currency.sign, currency.blank);
  }
  return rounded;
};
