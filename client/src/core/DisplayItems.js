import React from "react";

const DisplayItems = ({ items, noQty }) => {
  return items.map((item, index) => (
    noQty && !item.quantity.length ? 
      <Items index={index} item={item} key={index} />
    :
      !noQty ? 
        <Items index={index} item={item} key={index} />
      :
      null
  ));
};

const Items = ({ index, item }) => {
  return (
    <li key={index}>
      {item.item} - {item.quantity} - {item.notes}
    </li>
  );
}

export default DisplayItems;
