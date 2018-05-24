// @flow

import React from 'react';
import css from 'cleanslate.less';

type Props = {
  open: boolean,
  children: any,
  className: string
};

class FixedLayout extends React.PureComponent<Props> {
  static displayName = 'FixedLayout';

  render() {
    const { open, children, className } = this.props;
    return open ? (
      <div className={css.fixedLayout+' '+className}>
        { children }
      </div>
    ) : null;
  }
}

export default FixedLayout;
