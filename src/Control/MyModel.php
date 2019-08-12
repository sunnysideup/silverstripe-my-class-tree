<?php

namespace Sunnysideup\MyClassTre\Controller;

use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;

class MyModel extends Controller
{
    protected $hierarchy = [];

    private static $allowed_actions = [
        'index' => 'ADMIN'
    ];

    public function index()
    {
        $list = ClassInfo::subclassesFor(DataObject::class);
        foreach ($list as $class) {
            $ancestors = ClassInfo::ancestry($class);
            $ancestor = array_pop($ancestors);
            $ancestor = array_pop($ancestors);
            $childParentArray[$class] = $ancestor;
        }
        $childParentArray[DataObject::class] = null;
        $html = '<html lang="en">
    <head>
        <style>
            .jstree-closed > a {background-color: pink!important;}
        </style>
        <title>json tree example</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
      </head>
    <body>
        <div id="list"></div>
    <script>
    $(document).ready(
        function() {
            var json = ' . json_encode($this->parseTree($childParentArray)) . ';
            $("#list").jstree(
                {
                    "core" : {
                        "data" : json
                    }
                }
            );
        }
    );
    </script>
    </script>
    </body>
</html>';
        echo $html;
        //return DBField::create_field('HTMLText', $html)->raw();
    }

    protected function parseTree($tree, $root = null)
    {
        $return = [];
        # Traverse the tree and search for direct children of the root
        foreach ($tree as $child => $parent) {
            # A direct child is found
            if ($parent === $root) {
                # Remove item from tree (we don't need to traverse this again)
                unset($tree[$child]);
                # Append the child into result array and parse its children
                $return[] = [
                    'text' => $child,
                    'children' => $this->parseTree($tree, $child),
                ];
            }
        }
        return empty($return) ? [] : $return;
    }
}
