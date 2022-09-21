#Snap4City: IoT-Directory
# Copyright (C) 2017 DISIT Lab https://www.disit.org - University of Florence
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
import os

ATTRIBUTE_MASK = {
    "value_name": "-",
    "data_type": "-",
    "value_type": "-",
    "value_unit": "-",
    "healthiness_criteria": "refresh_rate",
    "healthiness_value": "300",
    "editable": "0",
    "checked": "False",
    "raw_attribute": "{}"
}

FONT_TUPLE = ("times", 11)

# Log should be a file


def add_to_log(message, log):
    log += message + "\n"


def create_folders(folders: list):
    for _folder in folders:
        os.makedirs(_folder, exist_ok=True)


# Pass a payload. See if the structure of it is a normalized structure, or just keyvalue.
def is_normalized(payload):
    for key in payload:
        if key == 'id' or key == 'type':
            continue
        if type(payload[key]) is dict:
            if "value" in payload[key].keys():
                return False
            else:
                return True


# Convert a normalized payload into keyvalue payload
def normalized_2_keyvalue(payload):
    out = {}
    meta_schema = {}
    for key in payload:
        if key == 'id' or key == 'type':
            out[key] = payload[key]
            continue

        if "value" in payload[key].keys():
            out[key] = payload[key]["value"]
        else:
            print(f"'value' not found in {key}")
        if "metadata" in payload[key].keys():
            if len(payload[key]["metadata"]) > 0:
                meta_schema[key] = payload[key]["metadata"]

    return out, meta_schema


def json_is_equals(json_a, json_b):
    def ordered(obj):
        if isinstance(obj, dict):
            return sorted((k, ordered(v)) for k, v in obj.items())
        if isinstance(obj, list):
            return sorted(ordered(x) for x in obj)
        else:
            return obj

    return ordered(json_a) == ordered(json_b)


def window_read_json(json_value, name, title="Json visualizer"):
    import tkinter as tk
    import json
    app = tk.Tk()
    app.title(title)

    json_val = json.dumps(json_value, indent="\t")
    label = tk.Label(app, text=f"{name}")
    text = tk.Text(app, font=FONT_TUPLE)
    label.pack()
    text.pack(expand=True, fill=tk.BOTH)
    text.insert(tk.END, json_val)
    text.config(state=tk.DISABLED, tabs="3")
    app.mainloop()


def ask_open_file(type, title=""):
    import tkinter as tk
    from tkinter import filedialog
    root = tk.Tk()
    if type == "json":
        root.title = title
        _temp = []

        def openFileJson(_temp):
            res = filedialog.askopenfile(title=title, filetypes=[
                                         ("Json files", "*.json")])
            _temp.append(res)

        tk.Button(root, text="Select a json file", command=openFileJson(_temp))
        root.mainloop()
        return _temp[0]


def window_edit_json(json_value, name, title="attribute_name"):
    import tkinter as tk
    app = tk.Tk()
    app.title(f"Edit attribute '{title}'")
    app.geometry("800x600")
    v_name_lbl = tk.Label(app, text="value_name:", font=FONT_TUPLE)
    value_name = tk.Entry(app)
    v_name_lbl.pack()
    value_name.pack()
    #value_name.insert(0, json_value["value_name"])
    v_type_lbl = tk.Label(app, text="value_type:", font=FONT_TUPLE)
    value_type = tk.Entry(app)
    v_type_lbl.pack()
    value_type.pack()
    #value_type.insert(0, json_value["value_type"])
    v_unit_lbl = tk.Label(app, text="value_unit:", font=FONT_TUPLE)
    value_unit = tk.Entry(app)
    v_unit_lbl.pack()
    value_unit.pack()
    #value_unit.insert(0, json_value["value_unit"])
    #name_lbl = tk.Label(app, text="value_name:")
    #value_name = tk.Entry(app)
    btn_save = tk.Button(app, text="Save")
    btn_undo = tk.Button(app, text="Undo")
    app.mainloop()
    #submit = Button(window, text='Submit', command=check).grid(row=3, column=1)


def window_edit_attribute(attribute, attribute_name, title, model, subdomain, domain, version, db, text=""):
    import tkinter as tk
    from tkinter import messagebox
    import json
    app = tk.Tk()
    app.title(title)

    json_val = json.dumps(attribute, indent="\t")
    label = tk.Label(app, text=f"Updating/Inserting (if new) attribute '{attribute_name}' in model '{model}', ver. '{version}'.\n "
                               f"Domain '{domain}'/ Subdomain '{subdomain}'\n"
                               f"{text}")
    text = tk.Text(app, font=FONT_TUPLE)
    label.pack()
    text.pack(expand=True, fill=tk.BOTH)
    text.insert(tk.END, json_val)
    text.config(tabs="3")

    def update_attribute():
        new_attribute = text.get("1.0", tk.END)
        new_attribute = json.loads(new_attribute)
        for _k in ATTRIBUTE_MASK.keys():
            if _k not in new_attribute.keys():
                print(f"Can't create this attribute. Key '{_k}' is missing...")
                return
        if not db.attribute_exists(attribute_name, model, subdomain, domain, version):
            db.create_attribute_if_not_exists(
                attribute_name, model, subdomain, domain, version)
        else:
            _ans = messagebox.askyesno(
                "Attribute already exists", "If you continue, you'll overwrite old definition")
            #window_read_json(db.get_attribute(attribute_name, model, subdomain, domain, version)[0][4], f"Old attribute '{attribute_name}'")
            if _ans == 0:
                return
        for _k in new_attribute:
            _value_to_set = new_attribute[_k]
            if isinstance(new_attribute[_k], dict):
                _value_to_set = json.dumps(new_attribute[_k])
            db.update_attribute_field(
                model, subdomain, domain, version, attribute_name, field=_k, value_to_set=_value_to_set)
        app.destroy()

    def reset():
        text.delete("1.0", tk.END)
        text.insert(tk.END, json_val)

    tk.Button(app, text="Update changes", command=update_attribute).pack()
    tk.Button(app, text="Reset", command=reset).pack()

    app.mainloop()


def load_db_config(file_location):
    import json
    with open(file_location) as dbconfig:
        return json.load(dbconfig)


def ask_choose_folder(title):
    import tkinter as tk
    from tkinter import filedialog
    root = tk.Tk()
    root.title(title)
    res = filedialog.askdirectory(title=title)
    root.destroy()
    if isinstance(res, str):
        return res + "/"
    else:
        return


def load_config(config_location):
    import json
    with open(config_location) as dbconfig:
        return json.load(dbconfig)
