// @flow

import React from 'react';
import css from 'cleanslate.less';

type Props = {
  open: boolean,
  children: any
};

class FixedLayout extends React.PureComponent<Props> {
  static displayName = 'FixedLayout';

  render() {
    const { open, children } = this.props;
    return open ? (
      <div className={css.fixedLayout}>
        { children }
      </div>
    ) : null;
  }
}

export default FixedLayout;
