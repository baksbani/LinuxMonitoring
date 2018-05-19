import psutil
import socket
import ipgetter
import datetime
import time
import os
import platform
from time import sleep
   
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
    }

    
    print(windows_usage)
    sleep(5)
    send_monitor_data()

# Recursive

def main():
    # Print value just for testing
    send_monitor_data()

if __name__ == "__main__":
    main()
