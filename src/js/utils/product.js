// @flow
import type { Product, Combinations, Combination, AttributeValues } from 'types';
import { find, propEq } from 'ramda';

export const getCombination = (combinations: Combinations, attributes: AttributeValues): ?Combination => {
  return find(propEq('attributes', attributes), combinations);
};

const fixImageUrl = (url) => {
  if (url) {
    if (url.indexOf('http') != 0) {
      return window.location.protocol + '//' + url;
    }
    return url.replace('http:', window.location.protocol);
  }
};

export const getImage = (product: Product, combination: ?Combination): ?string => {
  if (combination && combination.image) {
    return fixImageUrl(combination.image);
  }
  return fixImageUrl(product.image);
};

export const getPrice = (product: Product, combination: ?Combination): number => {
  return combination ? combination.price : product.price;
};
