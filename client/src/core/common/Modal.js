import React, { useEffect, Fragment, useState, useContext, useMemo } from "react";
import { useHistory } from "react-router-dom";
import { ClientContext } from "../common/ClientContext";
import { clientUpdateStatus } from "../common/ClientHelpers";
import { errorMessage } from "../common/Error";
import {
  updateClientStatus,
  saveClientVisitItem,
  getClients,
  clearCheckout,
  getItems,
  updateCheckinItems,
} from "./apiCore";
import { LBL_REQUESTS } from "./constants";
import '../css/styles.css';

const Modal = ({ modalId, client, type, refreshFunction, place }) => {
  const [error, setError] = useState(false);
  const [errorMsg, setErrMsg] = useState("");
  const [visitSaved, setVisitSaved] = useState(false);
  const [clients, setClients] = useContext(ClientContext);
  const [weightValue, setWeightValue] = useState("");
  const [numItemsValue, setNumItemsValue] = useState("");
  const [dateOfVisit, setDateOfVisit] = useState("");
  const [tabState, setTabState] = useState("edit");
  const [filteredItems, setFilteredItems] = useState([]);
  const [formValues, setFormValues] = useState({
    date_of_visit: '',
  });
  const [loading, setLoading] = useState(true);
  const [processingReq, setProcessingReq] = useState(false);
  const [internalNotes, setInternalNotes] = useState('');
  const [showOtherItemText, setShowOtherItemText] = useState(false);
  const history = useHistory();

  const [methodsOfPickup, setMethodsOfPickup] = useState([
    "Drive-Thru",
    "Walk-Up",
  ]);

  const handleNotesChange = (event) => {
    setInternalNotes(event.target.value);
  };

  const [selectedMethodOfPickup, setSelectedMethodOfPickup] = useState("");

  // all items at the selected place
  const [itemsCheckin, setItemsCheckin] = useState([]);
  const [clientItems, setClientItems] = useState([]);

  // all items at the selected place
  const [items, setItems] = useState([]);

  const [visit, setVisit] = useState({
    place_of_service: "",
    date_of_visit: "",
    item: "",
    notes: "",
    weight: "",
    numOfItems: "",
    itemType: ""
  });

  useEffect(() => {
    if (client?.items) {
      const noteLabel = 'notes';
      let holder = {};
      let filtered = [];

      setDateOfVisit(formatDate(new Date()));

      client.items.forEach((itm) => {
        if (itm.notes) {
          holder[itm.id] = itm.quantity;
          holder[itm.id + '-' + noteLabel] = itm.notes;
          setInternalNotes(itm.notes);
        } else {
          holder[itm.id] = itm.quantity;
        }
        filtered.push(itm.item);
      });

      setFilteredItems(filtered);

      setFormValues({
        ...formValues,
        ...holder,
        ['date_of_visit']: client.timestamp,
      });

      setLoading(false);
    }
  }, [client?.items]);

  useEffect(() => {
    if (visitSaved) {
      setTimeout(function() {
        setVisitSaved(false);
      }, 4000);
      resetFields();
      refreshFunction();
      handleGetClients();
    }
  }, [visitSaved]);

  const { date_of_visit, item, notes, weight, numOfItems } = visit;

  const resetFields = () => {
    setWeightValue("");
    setNumItemsValue("");
    // setDateOfVisit(""); 
    setVisit({ ...visit, notes: "" });
  };
  const toggleTab = (type) => {
    setTabState(type);
  };
  const handleInputChange = (event) => {
    const { name, value, type } = event.target;
    setFormValues({
      ...formValues,
      [name]: type === 'number' ? parseInt(value) : value,
    });
  };
  const getClientDate = useMemo(() => {
    let dateValue = new Date();

    if (client?.timestamp) {
      dateValue = client.timestamp;
    }

    return dateValue;
  }, []);

  useEffect(() => {
    if (type === "editCheckin") {
      setSelectedMethodOfPickup(client.methodOfPickup);

      if (client.placeOfService) {
        getItems(client.placeOfService).then(({ data }) => {  
          setItemsCheckin(data.items);
        });
      }
      let selectedItems = [];
      if (client.items) {
        client.items.map((i) => selectedItems.push(i.item));
      }
      setClientItems(selectedItems);
    }

    if (type === "checkout") {
      if (place) {
        getItems(place).then(({ data }) => {
          setItems(data.items);

          if (client.items !== undefined && client.items.length > 0) {
            let selectedItem = data.items.find(
              (e) => e.name === client.items[0].item
            );

            if (selectedItem !== undefined) {
              if (selectedItem.itemType === "Weight") {
                setVisit({
                  ...visit,
                  item: null,
                  weight: 0,
                  numOfItems: "",
                  itemType: "Weight"
                });
              } else {
                setVisit({
                  ...visit,
                  item: null,
                  weight: "",
                  numOfItems: 0,
                  itemType: "Number"
                });
              }
            }
          }
        });
      }
    }
  }, [client]);

  const formatDate = (date) => {
    if (!date) return '';

    if (typeof(date) == 'string') {
      date = new Date(date);
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const day = String(date.getDate()).padStart(2, '0');
  
    return `${year}-${month}-${day}`;
  };

  const handleChange = (name) => (event) => {
    if (name == "item") {
      // note: when item select menu changes this fires
      const itemType = document.querySelector(
        `option[value="${event.target.value}"]`
      ).dataset.type;

      if (itemType === "Weight") {
        setVisit({
          ...visit,
          item: event.target.value,
          weight: 0,
          numOfItems: "",
          itemType: 'Weight'
        });
      } else {
        setVisit({
          ...visit,
          item: event.target.value,
          weight: "",
          numOfItems: 0,
          itemType: 'Number'
        });
      }
    } else {
      if (name === "weight") {
        setWeightValue(event.target.value);
      }

      if (name === "numOfItems") {
        setNumItemsValue(event.target.value);
      }

      if (name === "date_of_visit") {
        console.log("date of visit", event.target.value);
        setDateOfVisit(event.target.value);
      }

      setVisit({ ...visit, [name]: event.target.value });
    }
  };

  const handleServing = (e) => {
    e.preventDefault();
    let hasError = false;
    let errorMessage = '';
    client.status = type;

    if (type == 'checkout') {
      clients.serving.forEach(item => {
        if (item.c_id == client.c_id) {
          item.items.forEach(itm => {
            if (!itm.quantity && itm.item !== LBL_REQUESTS) {
              hasError = true;
              errorMessage = `Error: User can't have empty items!`;
            }
          });
        }
      });

      if (!dateOfVisit) {
        hasError = true;
        errorMessage = `Error: Date of Visit can't be empty!`;
      }

      if (!hasError) {
        client.timestamp = new Date(dateOfVisit);
        updateClientStatus(client).then((response) => {
          refreshFunction();
        });
      } else {
        // throw error message
        alert(errorMessage);
      }
    } else {
      updateClientStatus(client).then((response) => {
        refreshFunction();
      });
    }
  };

  const refreshPage = (e) => {
    history.push('/foodpantry?active=serving');
    window.location.reload(false);
  };

  const handleVisitBeforeCheckout = (e) => {
    e.preventDefault();
    setProcessingReq(true);
    if (tabState == 'edit') {
      console.log('formValues', formValues);
      let counter = 0;

      if (client?.items) {
        client.items.map((itm) => {
          const visit = {
            id: itm.id,
            c_id: itm.c_id,
            place_of_service: itm.placeOfService,
            date_of_visit: formValues.date_of_visit,
            item: itm.item,
            notes: Object.keys(formValues).includes(itm.id + '-notes') ? formValues[itm.id + '-notes'] : '',
            weight: itm.itemType == 'Weight' ? formValues[itm.id] : '',
            numOfItems: itm.itemType == 'Number' ? formValues[itm.id] : '',
            place_of_service: itm.place_of_service
          };
        
          saveClientVisitItem(visit).then(() => {
            counter++;
            if (counter >= client.items.length) {
              console.log('inside setting visit saved')
              setVisitSaved(true);
              setProcessingReq(false);
            }
          });
        });
      }
    }

    if (tabState == 'add') {
      if (item === null) {
        alert("Item can not be empty");
        return false;
      }

      if (weight === 0) {
        if (weightValue === "") {
          alert("Weight value can not be empty");
          return false;
        }
      }

      if (numOfItems === 0) {
        if (numItemsValue === "") {
          alert("Number of items can not be empty");
          return false;
        }
      }

      if (dateOfVisit.length <= 0) {
        alert("Error: Date of Visit can't be empty!");
        return false;
      }

      const visit = {
        // id: client.id,
        c_id: client.c_id,
        place_of_service: client.placeOfService,
        date_of_visit: dateOfVisit,
        item,
        notes,
        weight,
        numOfItems
      };

      saveClientVisitItem(visit).then((response) => {
        setVisitSaved(true);
        setProcessingReq(false);
        refreshPage();
        // setFilteredItems([...filteredItems, item]);
      });
    }
  };

  const handleGetClients = () => {
    getClients(place).then((response) => {
      if (response) {
        if (response.data.error) {
          // response can come back as error if there are no other clients in this place
          // (checkedIn, serving) after being cleared from checkout
          setClients((prevClients) => {
            return { ...prevClients, checkedOut: [] };
          });
        } else {
          const { checkedIn, serving, checkedOut } = clientUpdateStatus(
            response.data.clients
          );

          setClients((prevClients) => {
            return {
              ...prevClients,
              place,
              checkedIn,
              serving,
              checkedOut,
            };
          });
        }
      } else {
        setError(true);
        setErrMsg("No response from server");
      }
    });
  };

  const handleClearCheckout = (e) => {
    e.preventDefault();

    clearCheckout(place).then((response) => {
      handleGetClients();
    });
  };

  const handleItems = (name) => (event) => {
    if (event.target.checked) {
      setClientItems([...clientItems, event.target.value]);
      if (event.target.value == LBL_REQUESTS) {
        setShowOtherItemText(true);
      }
    } else {
      let clientItemsFilter = clientItems.filter(
        (item) => item !== event.target.value
      );
      setClientItems(clientItemsFilter);
      if (event.target.value == LBL_REQUESTS) {
        setShowOtherItemText(false);
      }
    }
  };

  const handleEditCheckin = () => {
    if (clientItems.length === 0 || selectedMethodOfPickup === "") {
      alert("Please fill in all the information");
      return false;
    }

    updateCheckinItems(
      client.c_id,
      client.placeOfService,
      selectedMethodOfPickup,
      clientItems,
      internalNotes
    ).then((result) => {
      console.log("result ", result);
      history.push('/foodpantry');
      window.location.reload(false);
    });
  };

  const editCheckin = () => (
    <div
      className="modal fade"
      id={modalId}
      tabIndex="-1"
      role="dialog"
      aria-labelledby="clientModalTitle"
      aria-hidden="true"
    >
      <div className="modal-dialog" role="document">
        <div className="modal-content">
          <div className="modal-header">
            {error && errorMessage(errorMsg)}
            <h5 className="modal-title" id="clientModalTitle">
              Edit
            </h5>
            <button
              type="button"
              className="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div className="modal-body">
            <div className="form-group col-sm">
              <label>
                <strong>Method of pickup</strong>
              </label>
              <select
                onChange={(e) => setSelectedMethodOfPickup(e.target.value)}
                className="custom-select"
                id="methodsOfPickup"
              >
                <option defaultValue value="0">
                  Choose...
                </option>
                {client &&
                  methodsOfPickup.map((method, index) => {
                    if (method === selectedMethodOfPickup) {
                      return (
                        <option key={index} selected value={method}>
                          {method}
                        </option>
                      );
                    }
                    return (
                      <option key={index} value={method}>
                        {method}
                      </option>
                    );
                  })}
              </select>
            </div>
            <div className="form-group col-sm">
              <label>
                <strong>Items</strong>
              </label>

              {itemsCheckin &&
                itemsCheckin.map((item, index) => {
                  let selectedItem = clientItems.find((i) => i === item.name);
                  if (selectedItem) {
                    return (
                      <div className="form-check" key={index}>
                        <input
                          onChange={handleItems(index)}
                          className="form-check-input"
                          type="checkbox"
                          value={item.name}
                          id={index}
                          checked
                        />
                        <label className="form-check-label" htmlFor={index}>
                          {item.name}
                        </label>
                        {item.name == LBL_REQUESTS ?
                          <div>
                            <textarea 
                              style={{ width: '100%' }} 
                              placeholder="Enter notes here" 
                              maxLength="250"
                              rows="8"
                              value={internalNotes}
                              onChange={handleNotesChange}></textarea>
                          </div>
                        : null}
                      </div>
                    );
                  }
                  return (
                    <div className="form-check" key={index}>
                      <input
                        onChange={handleItems(index)}
                        className="form-check-input"
                        type="checkbox"
                        value={item.name}
                        id={index}
                      />
                      <label className="form-check-label" htmlFor={index}>
                        {item.name}
                      </label>
                      {showOtherItemText && item.name == LBL_REQUESTS ?
                        <div>
                          <textarea 
                            style={{ width: '100%' }} 
                            placeholder="Enter notes here" 
                            maxLength="250"
                            rows="8"
                            value={internalNotes}
                            onChange={handleNotesChange}></textarea>
                        </div>
                      : null}
                    </div>
                  );
                })}
            </div>
          </div>
          <div className="modal-footer">
            <button
              type="button"
              className="btn btn-secondary"
              data-dismiss="modal"
            >
              Cancel
            </button>
            <button
              onClick={handleEditCheckin}
              className="btn btn-primary"
              data-dismiss="modal"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  );

  const serving = () => (
    <div
      className="modal fade"
      id={modalId}
      tabIndex="-1"
      role="dialog"
      aria-labelledby="clientModalTitle"
      aria-hidden="true"
    >
      <div className="modal-dialog" role="document">
        <div className="modal-content">
          <div className="modal-header">
            {error && errorMessage(errorMsg)}
            <h5 className="modal-title" id="clientModalTitle">
              Move{" "}
              <span style={{ color: "green" }}>
                {client.fname} {client.lname}
              </span>{" "}
              to {type}?
            </h5>
            <button
              type="button"
              className="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div className="modal-body">They will be moved to the {type} tab</div>
          <div className="modal-footer">
            <button
              type="button"
              className="btn btn-secondary"
              data-dismiss="modal"
            >
              Cancel
            </button>
            <button
              onClick={handleServing}
              className="btn btn-primary"
              data-dismiss="modal"
            >
              Yes
            </button>
          </div>
        </div>
      </div>
    </div>
  );

  const checkout = () => (
    <div
      className="modal fade"
      id={modalId}
      tabIndex="-1"
      role="dialog"
      aria-labelledby="clientModalTitle"
      aria-hidden="true"
    >
      <div className="modal-dialog" role="document">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title" id="clientModalTitle">
              Add visit for{" "}
              <span style={{ color: "green" }}>
                {client.fname} {client.lname}
              </span>{" "}
              and move to {type}?
            </h5>

            <button
              type="button"
              className="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div className="modal-body">
            <div
              className="alert alert-success"
              role="alert"
              style={{ display: visitSaved ? "block" : "none", opacity: visitSaved ? '1' : 0 }}
            >
              Client visit has been saved!
            </div>

            <ul className="nav nav-tabs tabs-serving-modal" >
              <li className="nav-item">
                <button 
                  className={`nav-link ${tabState == 'edit' ? 'active' : ''}`}
                  type="button" 
                  onClick={() => toggleTab('edit')}>Edit</button>
              </li>
              <li className="nav-item">
                <button 
                  className={`nav-link ${tabState == 'add' ? 'active' : ''}`}
                  type="button" 
                  onClick={() => toggleTab('add')}>Add New</button>
              </li>
            </ul>

            <div className="card">
              {tabState == 'edit' && !loading ?
                <div className="card-body">
                  <div className="form-group col-sm" style={{ paddingLeft: 0 }}>
                  <label htmlFor="dateOfVisit">
                    <strong>Date of Visit</strong>
                  </label>
                  <input
                    onChange={handleInputChange}
                    // onChange={handleChange("date_of_visit")}
                    name="date_of_visit"
                    type="date"
                    className="form-control"
                    id="dateOfVisit"
                    value={formatDate(formValues.date_of_visit)}
                  />
                </div>
                  <div className="row">
                    <div className="col-md-6">
                      <strong>Items</strong>
                    </div>
                    <div className="col-md-6">
                      {/* <strong style={{ paddingLeft: '15px' }}>Value</strong> */}
                    </div>
                  </div>
                  {client?.items ?
                    client.items.map((itm, index) => {
                      return (
                        <div className="row" key={index}>
                          <div className="col-md-6">
                            {itm.item}
                            </div>
                          <div className="col-md-6">                            
                            {/* {itm.quantity} */}
                            {itm.itemType == "Weight" ? 
                              <div
                                className="form-group col-sm"
                              >
                                {/* <label htmlFor="weight">
                                  <strong>Weight</strong>
                                </label> */}
                                <input
                                  type="text"
                                  name={itm.id}
                                  className="form-control"
                                  id="weight"
                                  onChange={handleInputChange}
                                  // onChange={handleChange("weight")}
                                  value={formValues[itm.id]}
                                  required
                                  placeholder="enter weight"
                                />
                              </div>
                            : null}
                            {itm.itemType == "Number" ? 
                              <div
                                className="form-group col-sm"
                              >
                                {/* <label htmlFor="numOfItems">
                                  <strong>Number of items</strong>
                                </label> */}
                                <input
                                  type="text"
                                  name={itm.id}
                                  className="form-control"
                                  id="numOfItems"
                                  // onChange={handleChange("numOfItems")}
                                  onChange={handleInputChange}
                                  value={formValues[itm.id]}
                                  required
                                  placeholder="enter quantity"
                                />
                              </div>
                            : null}
                          </div>
                          {itm.item == LBL_REQUESTS ?
                            <div className="col-md-12">
                              <div className="form-group col-sm" style={{ paddingLeft: 0 }}>
                                <textarea
                                  name={`${itm.id}-notes`}
                                  // onChange={handleChange("notes")}
                                  onChange={handleInputChange}
                                  className="form-control rounded-0"
                                  id="notes"
                                  rows="3"
                                  value={formValues[itm.id + '-notes']}
                                  placeholder="Enter notes"
                                >
                                  {formValues[itm.id + '-notes']}
                                </textarea>
                              </div>
                            </div>
                          : null}
                        </div>
                      )
                    })
                  : null}
                </div>
              :
                <div className="card-body" style={{ paddingLeft: '5px' }}>
                  {/* edit card body content */}
                  <div className="form-group col-sm">
                    <label htmlFor="dateOfVisit">
                      <strong>Date of Visit</strong>
                    </label>
                    <input
                      onChange={handleChange("date_of_visit")}
                      type="date"
                      className="form-control"
                      id="dateOfVisit"
                      value={dateOfVisit}
                    />
                  </div>
                  <div className="form-group col-sm">
                    <label htmlFor="item">
                      <strong>Item</strong>
                    </label>

                    <div className="input-group mb-3">
                      {items && (
                        <select
                          onChange={handleChange("item")}
                          className="custom-select"
                          id="item"
                        >
                          <option
                            data-type="null"
                            value="null"
                          >
                            -- Select an Item --
                          </option>
                          {items.filter(itm => !filteredItems.includes(itm.name)).map((i, index) => {
                            if (item !== "") {
                              if (i.name === item) {
                                return (
                                  <option
                                    key={index}
                                    data-type={i.itemType}
                                    value={i.name}
                                  >
                                    {i.name}
                                  </option>
                                );
                              }
                            }

                            return (
                              <option
                                key={index}
                                data-type={i.itemType}
                                value={i.name}
                              >
                                {i.name}
                              </option>
                            );
                          })}
                        </select>
                      )}
                    </div>
                  </div>
                  {visit.itemType == "Weight" ? 
                    <div
                      className="form-group col-sm"
                    >
                      <label htmlFor="weight">
                        <strong>Weight</strong>
                      </label>
                      <input
                        type="number"
                        className="form-control"
                        id="weight"
                        onChange={handleChange("weight")}
                        value={weightValue}
                        required
                      />
                    </div>
                  : null}
                  {visit.itemType == "Number" ? 
                    <div
                      className="form-group col-sm"
                    >
                      <label htmlFor="numOfItems">
                        <strong>Number of items</strong>
                      </label>
                      <input
                        type="number"
                        className="form-control"
                        id="numOfItems"
                        onChange={handleChange("numOfItems")}
                        value={numItemsValue}
                        required
                      />
                    </div>
                  : null}
                  <div className="form-group col-sm">
                    <label htmlFor="notes">
                      <strong>Notes</strong>
                    </label>
                    <textarea
                      onChange={handleChange("notes")}
                      className="form-control rounded-0"
                      id="notes"
                      rows="3"
                      value={notes}
                    >
                      {notes}
                    </textarea>
                  </div>
                  {/*  */}
                </div>
              }
            </div>
          </div>
          {/* end of modal body */}
          <div className="modal-footer">
            <button
              type="button"
              className="btn btn-secondary"
              data-dismiss="modal"
              onClick={refreshPage}
            >
              Cancel
            </button>
            <button
              onClick={handleVisitBeforeCheckout}
              className="btn btn-success"
              disabled={processingReq}
            >
              Save
            </button>
            <button
              onClick={handleServing}
              className="btn btn-primary"
              data-dismiss="modal"
            >
              Move to checkout
            </button>
          </div>
        </div>
      </div>
    </div>
  );

  const clearClients = () => (
    <div
      className="modal fade"
      id={modalId}
      tabIndex="-1"
      role="dialog"
      aria-labelledby="clientModalTitle"
      aria-hidden="true"
    >
      <div className="modal-dialog" role="document">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title" id="clientModalTitle">
              Clear checked out clients?
            </h5>
            <button
              type="button"
              className="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div className="modal-body">The list will be cleared</div>
          <div className="modal-footer">
            <button
              type="button"
              className="btn btn-secondary"
              data-dismiss="modal"
            >
              Cancel
            </button>
            <button
              onClick={handleClearCheckout}
              className="btn btn-primary"
              data-dismiss="modal"
            >
              Yes
            </button>
          </div>
        </div>
      </div>
    </div>
  );

  return (
    <Fragment>
      {type == "editCheckin" && editCheckin()}
      {type == "serving" && serving()}
      {type == "checkout" && checkout()}
      {type == "clearcheckout" && clearClients()}
    </Fragment>
  );
};

export default Modal;
