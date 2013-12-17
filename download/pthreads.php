#!/opt/phpzts/bin/php
<?php


class myThread extends Thread {
    public function run() {
        for($i=0;$i<3;$i++) {
            print 'id is ('.$this->getThreadId().")sleep 2s\n";
            sleep(2);
            print "continue\n";
        }
    }
}

class myWorker extends Worker {
    public function run() {
        $k = 0;
        while($k<3) {
            print 'id is ('.$this->getThreadId().")sleep 2s\n";
            sleep(2);
            print "continue\n";
            $k++;
        }
    }
}
$ts = [];
for($i=0;$i<3;$i++) {
$ts[$i] = new myThread();
$ts[$i]->start();
}