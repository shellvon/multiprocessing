# encoding=utf8
# author=shellvon<iamshellvon@gmail.com>

import re
import subprocess


def main():
    cmd = 'ipcs -s|grep `whoami`'
    (ipcs_s, _) = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE).communicate()
    cmd = 'ipcs -m|grep `whoami`'
    (ipcs_m, _) = subprocess.Popen(
        cmd, shell=True, stdout=subprocess.PIPE).communicate()
    if ipcs_s:
        ids = map(
            lambda x: re.sub('\s+', ' ', x).split(' ')[1], ipcs_s.split('\n')[:-1])
        print 'get %d active semaphores' % len(ids)
        for pid in ids:
            subprocess.Popen(
                'ipcrm -s %s' % pid, shell=True, stdout=subprocess.PIPE).communicate()
    if ipcs_m:
        ids = map(
            lambda x: re.sub('\s+', ' ', x).split(' ')[1], ipcs_m.split('\n')[:-1])
        print 'get %d active shared memory segments' % len(ids)
        for pid in ids:
            subprocess.Popen(
                'ipcrm -m %s' % pid, shell=True, stdout=subprocess.PIPE).communicate()
    print 'finished.'
if __name__ == '__main__':
    main()
