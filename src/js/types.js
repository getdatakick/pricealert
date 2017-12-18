// @flow
export type Step = 'product' | 'email';

export type State = {
  width: number,
  message: ?string,
  show: boolean,
  step: Step,
  email: string,
  slider: number,
  attributes: AttributeValues
}

export type Translations = {
  cancel: string,
  create_alert: string,
  current_price: string,
  alert_me_when_price_drops_to: string,
  your_email_address: string,
  alert_has_been_created: string,
  combination_does_not_exists: string
}

export type Attribute = {
  id: number,
  name: string,
  type: string,
  values: Array<AttributeValue>
}

export type Attributes = Array<Attribute>;

export type AttributeValue = {
  id: number,
  name: string
}

export type AttributeValues = {
  [ number ]: number
};

export type Combination = {
  id: number,
  attributes: AttributeValues,
  price: number,
  quantity: number,
  image: string
}

export type Combinations = Array<Combination>

export type Customer = {
  id: ?number,
  email: ?string
}

export type Product = {
  id: number,
  name: string,
  image: string,
  price: number,
  attributes: Attributes,
  combinations: Combinations
}

export type Config = {
  theme: 'light' | 'dark',
  separator: string,
  defaultDiscount: number,
  minDiscount: number,
  showFullScale: boolean,
  step: number
}


export type Currency = {
  sign: string,
  blank: string,
  format: string
}

export type Settings = {
  customer: {
    id: ?number,
    email: ?string
  },
  product: Product,
  config: Config,
  currency: Currency,
  translation: Translations
}
