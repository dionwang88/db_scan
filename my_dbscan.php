<?php
/**
 * Created by PhpStorm.
 * User: wangqian
 * Date: 6/12/16
 * Time: 12:37
 */
class Db_Scan
{
    private $clusters;
    private $noises;
    private $in_a_cluster;
    private $data;
    private $eps;
    private $MinPts;

    public function __construct($data, $eps, $MinPts)
    {
        $this->clusters = array();
        $this->noises = array();
        $this->in_a_cluster = array();

        $this->data = $data;
        $this->eps = $eps;
        $this->MinPts = $MinPts;
    }

    function dbscan()
    {
        $c = 0;
        foreach ($this->data as $point_id => $value) {//for each point P in dataset D
            $neighbor_points = $this->region_query($point_id);
            if (count($neighbor_points) < $this->MinPts)
                $this->noises[] = $point_id; //mark P as NOISE
            elseif(!in_array($point_id, $this->in_a_cluster)) {
                $this->expand_cluster($point_id, $c, $neighbor_points);
                $c += 1;
            }
        }
        return $this->clusters;
    }

    function expand_cluster($point, $c, $neighbor_points)
    {
        $this->clusters[$c] = array();
        $this->clusters[$c][] = $point;
        $this->in_a_cluster[] = $point;

        // get the first point in neighbors
        $neighbor_point = reset($neighbor_points);
        while ($neighbor_point) {
            $neighbor_points2 = $this->region_query($neighbor_point);
            if (count($neighbor_points2) >= $this->MinPts){
                foreach ($neighbor_points2 as $np2) {
                    if (!in_array($np2, $neighbor_points)){
                        $neighbor_points[] = $np2;
                    }
                }
            }
            if (!in_array($neighbor_point, $this->in_a_cluster))
            {
                $this->clusters[$c][] = $neighbor_point;
                $this->in_a_cluster[] = $neighbor_point;
            }
            $neighbor_point = next($neighbor_points);
        }
    }

    function region_query($point)
    {
        $arr = $this->data[$point];
        $ret = array();
        foreach ($arr as $key => $distance) {
            if ($point !== $key) {
                if ($distance <= $this->eps) {
                    $ret[] = $key;
                }
            }
        }
        return $ret;
    }

    function get_noise(){
        return $this->noises;
    }

    function print_array_list($arr){
        foreach ($arr as $a) {
            print "" . $a . ",";
        }
        print "\n";
    }

    function print_array_map($arr){
        foreach ($arr as $key=>$value) {
            print "" . $key . ":" . $value . ",";
        }
        print "\n";
    }
}

function get_matrix()
{
    $arr1 = array();

    for ($i = 1; $i <= 20; $i++) {
        print($i . ", [");
        $arr2 = array();
        for ($j = 1; $j <= 20; $j++) {
            $arr2[$j] = rand(1, 50);
            print("" . $j . "=>" . $arr2[$j] . ",");
        }
        $arr1[$i] = $arr2;
        print("]\n");
    }
    return $arr1;
}

function test_dbscan()
{
    $data = array();
    $data[1] = array(0, 3, 3, 4, 5);
    $data[2] = array(3, 0, 3, 2, 1);
    $data[3] = array(3, 3, 0, 4, 5);
    $data[4] = array(4, 2, 4, 0, 5);
    $data[5] = array(5, 1, 5, 5, 0);

    $dbscan = new Db_Scan($data, 2, 2);
    $cluster = $dbscan->dbscan();

    print "clusters: \n";
    foreach ($cluster as $key => $value) {
        print $key . ": ";
        foreach ($value as $v) {
            print $v . ",";
        }
        print "\n";
    }

    print "noises: \n";
    $dbscan->print_array_list($dbscan->get_noise());
}

test_dbscan();

?>