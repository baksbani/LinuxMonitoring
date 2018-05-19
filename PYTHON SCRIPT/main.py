import psutil
import socket
import ipgetter
import datetime
import time
import os
import platform
from time import sleep

from os import popen
from json import dumps
from time import sleep
from requests import post
from sys import exit


def check_user_exists():
    inputdata = raw_input('Enter your email: ')
    email = dumps({'email': inputdata}, ensure_ascii='False')
    urlcheck = "http://localhost.monitorbeta.com/rest/v1/checkemail"
    headers = {'content-type': 'application/json'}
    r = post(urlcheck, data=email, headers=headers)
    response = r.text
    if(response == "emailcheck_working"):
        check_auth_file()
    else:
        print ("You are not registered")
        exit()


def check_auth_file():
    global auth_code
    try:
        with open('/opt/linuxmonitor/serverauth.txt', 'r') as auth_file:
            auth_code = auth_file.read()
            auth = dumps({'auth': auth_code}, ensure_ascii='False')
            urlcheck = "http://localhost.monitorbeta.com/rest/v1/checkauth"
            headers = {'content-type': 'application/json'}
            r = post(urlcheck, data=auth, headers=headers)
            response = r.text
            if (response == "auth_result_working"):
                send_monitor_data()
            else:
                print ("Authentification code is not correct")
                choice = raw_input(
                    'Do you want to configure your server auth code [Y/N]: ')
                if (choice == "y") or (choice == "Y"):
                    configure_auth_file()
                elif (choice == "n") or (choice == "N"):
                    print ("Missing Authentification Code error will stay")
                    exit(0)
                else:
                    print ("Invalid select")
                    exit(0)
    except IOError as e:
        print ("Missing authentification file. Error: ", e)
        choice = raw_input(
            'Do you want to create and configure your server auth code [Y/N]: ')
        if (choice == "y") or (choice == "Y"):
            create_auth_file()
        elif (choice == "n") or (choice == "N"):
            print ("Missing Authentification Code error will stay")
            exit(0)
        else:
            print ("Invalid select")
            exit(0)


def configure_auth_file():
    try:
        with open('/opt/linuxmonitor/serverauth.txt', 'r+') as auth_file:
            auth_file.truncate()
            your_auth = raw_input(
                "Enter your server's authentification code: ")
            auth_file.write(your_auth)
            print (
                "-------------------------------------------------------------------")
            print (
                "#                  Your new auth code is saved.                   #")
            print (
                "-------------------------------------------------------------------")
            print ("Script Restart in 3 seconds...")
            auth_file.close()
            sleep(3)
            check_auth_file()
    except IOError as e:
        print ("Missing authentification file. Error: ", e)
        exit()


def create_auth_file():
    try:
        f = open("/opt/linuxmonitor/serverauth.txt", "w+")
        print ("-------------------------------------------------------------------")
        print ("#                   Your auth file is created.                    #")
        print ("-------------------------------------------------------------------")
        print ("Configuration starts in 3 seconds...")
        f.close()
        sleep(3)
        configure_auth_file()
    except IOError as e:
        print ("Error: ", e)
        print ("Remember to run this script as root (sudo python main.py): ")
        exit()


def send_monitor_data():

    windows_usage = {
        'os_name': platform.system(),  # string
        'os_version': platform.version(),  # string

        'cpu_model': platform.processor(),  # CPU Model
        'cpu_architecture': platform.architecture(),  # CPU Archi..
        'cpu_cores': psutil.cpu_count(),  # Number of cpus
        'cpu_threads': psutil.cpu_count(),  # Number of threads
        'cpu_percentage': psutil.cpu_percent(),  # CPU Perc
        'pid_running': len(psutil.pids()),  # Number of active PIDs

        'hostname': socket.gethostname(),  # string
        'internal_ip': socket.gethostbyname(socket.gethostname()),  # string
        'external_ip': ipgetter.myip(),  # string

        'ram_total': psutil.virtual_memory()[0],  # GB
        'ram_used': psutil.virtual_memory()[3],  # GB
        'ram_free': psutil.virtual_memory()[4],  # GB
        'ram_shared': psutil.virtual_memory()[2],  # GB
        'ram_available': psutil.virtual_memory()[1],  # GB
        'ram_buff': psutil.virtual_memory()[4],  # GB

        'swap_total': psutil.swap_memory()[0],  # GB
        'swap_used': psutil.swap_memory()[1],  # GB
        'swap_free': psutil.swap_memory()[2],  # GB

        'total_hdd': psutil.disk_usage('/')[0],  # All SDx partitions total in GB
        'used_hdd': psutil.disk_usage('/')[1],  # All SDx partitions usage in GB
        'available_hdd': psutil.disk_usage('/')[2],  # Free space in GB

        'uptime': datetime.datetime.fromtimestamp(time.time()-psutil.boot_time()).strftime('%H:%M'),  # Uptime in H:M format
    }, ensure_ascii='False')

    # Print value just for testing
    print(windows_usage)

    # Post data to server
    url = "http://localhost.monitorbeta.com/rest/v1/endpoint"
    headers = {'content-type': 'application/json'}
    r = post(url, data=windows_usage, headers=headers)

    # Print response just for testing
    print(r.text)

    sleep(5)

    # Recursive
    send_monitor_data()


def main():
    check_user_exists()


if __name__ == "__main__":
    main()
   

      
