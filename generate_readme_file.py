#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
import getopt
import json
import os
import codecs
from pprint import pprint

HEAD_TAG = "# "
API_TAG  = "## "
TITILE_TAG = "#### "
PARAM_HEADER = "| Name | Type | Required | Description |\n| --- | --- | --- |--- |\n"
RESPONSE_TEMPLATE = "| Name | Type | Nullable | Description | Format|\n| --- | --- | --- | --- | --- |\n"
RESPONSE_SAMPLE_TEMPLATE = "```json \n\n ```\n"
ERROR_RESPONSE_TEMPLATE = "| Name | Error Code | Message |\n| --- | --- | --- |\n"

''' Sample
"endpoints": [
    {
        "name": "Auth API",
        "methods": [
            {
                "MethodName": "Auth",
                "Synopsis": "Request access token from playbasis server.",
                "HTTPMethod": "POST",
                "URI": "/Auth",
                "RequiresOAuth": "N",
                "parameters": [
                    {
                      "Name": "api_key",
                      "Required": "Y",
                      "Default": "",
                      "Type": "string",
                      "Description": "api key issued by Playbasis"
                    },
                    {
                      "Name": "api_secret",
                      "Required": "Y",
                      "Default": "",
                      "Type": "string",
                      "Description": "api secret issued by Playbasis"
                    }
                ]
            },
            {
                "MethodName": "Renew",
                "Synopsis": "Create a new token and discard the current token",
                "HTTPMethod": "POST",
                "URI": "/Auth/renew",
                "RequiresOAuth": "N",
                "parameters": [
                    {
                      "Name": "api_key",
                      "Required": "Y",
                      "Default": "",
                      "Type": "string",
                      "Description": "api key issued by Playbasis"
                    },
                    {
                      "Name": "api_secret",
                      "Required": "Y",
                      "Default": "",
                      "Type": "string",
                      "Description": "api secret issued by Playbasis"
                    }
                ]
            }
        ]
    },
]
'''
def process(in_json):
    with codecs.open(in_json, encoding='utf-8', errors="ignore") as data_file:
        data = json.load(data_file)
        endpoints = data["endpoints"]
        for endpoint in endpoints:
            file_name = endpoint["name"]
            file_path = "application/controllers/" + file_name + ".md"
            try:
                os.remove(file_path)
            except OSError:
                pass

            md_file = codecs.open(file_path,'w', encoding='utf-8', errors="ignore")
            md_file.write(HEAD_TAG + file_name + "\n")

            methods = endpoint["methods"]
            for method in methods:
                md_file.write(API_TAG + method["MethodName"] + "\n")
                md_file.write(method["Synopsis"] + "\n")

                md_file.write(TITILE_TAG + "HTTPMethod" + "\n")
                md_file.write(method["HTTPMethod"] + "\n")

                md_file.write(TITILE_TAG + "URI" + "\n")
                md_file.write(method["URI"] + "\n")

                md_file.write(TITILE_TAG + "RequiresOAuth" + "\n")
                if method["RequiresOAuth"] == 'N' or method["RequiresOAuth"] == 'n':
                    md_file.write("NO" + "\n")
                else:
                    md_file.write("YES" + "\n")

                md_file.write(TITILE_TAG + "Parameters" + "\n")
                md_file.write(PARAM_HEADER)

                parameters = method["parameters"]
                for parameter in parameters:
                    md_file.write("| ")
                    md_file.write(parameter["Name"] + " | ")
                    md_file.write(parameter["Type"] + " | ")
                    md_file.write(("NO | ") if parameter["Type"] == 'N' or parameter["Type"] == 'n' else "YES | ")
                    md_file.write(parameter["Description"] + " | \n")

                md_file.write(TITILE_TAG + "Response" + "\n")
                md_file.write(RESPONSE_TEMPLATE)
                md_file.write(TITILE_TAG + "Response Example" + "\n")
                md_file.write(RESPONSE_SAMPLE_TEMPLATE)
                md_file.write(TITILE_TAG + "Error Response" + "\n")
                md_file.write(ERROR_RESPONSE_TEMPLATE)

            md_file.close()

def main():
    # parse command line options
    try:
        opts, args = getopt.getopt(sys.argv[1:], "h", ["help"])
    except getopt.error, msg:
        print msg
        print "for help use --help"
        sys.exit(2)
    # process options
    for o, a in opts:
        if o in ("-h", "--help"):
            print __doc__
            sys.exit(0)
    # process arguments
    for arg in args:
        process(arg) # process() is defined elsewhere

if __name__ == "__main__":
    main()
