<?php

namespace Sunnysideup\MyClassTre\Controller;

use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;

class MyModel extends Controller
{
    private static $allowed_actions = [
        'index' => 'ADMIN',
        'raw' => 'ADMIN',
    ];

    public function raw()
    {
        $html = $this->array2ul($this->hierarchyOfClasses(DataObject::class));
        echo DBField::create_field('HTMLText', $html)->raw();
        return [];
    }

    public function index()
    {
        $list = $this->hierarchyOfClasses(DataObject::class);

        $html = '<html lang="en">
    <head>
        <style>
            .jstree-closed > a {background-color: pink!important;}
            .hideme {display:none;}
            a:hover > .hideme {display: inline;}
        </style>
        <title>json tree example</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
      </head>
    <body>
        <p>
            <a href="#" onclick="javascript: $(\'#list\').jstree(\'open_all\'); return false;">expand all</a>
        </p>
        <div id="list"></div>
    <script>
    $(document).ready(
        function() {
            var json = ' . json_encode($list) . ';
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
        // return DBField::create_field('HTMLText', $html)->raw();
    }

    protected function hierarchyOfClasses($className = '')
    {
        $list = ClassInfo::subclassesFor($className);
        foreach ($list as $class) {
            $ancestors = ClassInfo::ancestry($class);
            $ancestor = array_pop($ancestors);
            $ancestor = array_pop($ancestors);
            $childParentArray[$class] = $ancestor;
        }
        $childParentArray[DataObject::class] = null;

        return $this->parseTree($childParentArray);
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
                $short = ClassInfo::shortName($child);
                $longMinusShort = rtrim(str_replace($short, '', $child), '\\');
                # Append the child into result array and parse its children
                $return[] = [
                    'text' => '<strong>'.$short.'</strong> <span class="hideme"> - '.$longMinusShort.'<span>',
                    'children' => $this->parseTree($tree, $child),
                ];
            }
        }
        return empty($return) ? [] : $return;
    }

    /**
     * Render an array|object as HTML list (UL > LI)
     *
     * @param mixed $data List items
     *
     * @return string
     */
    public static function array2ul($data) :string {
        $return = '';
        foreach ($data as $index => $item) {
            if (!is_string($item)) {
                $return .= '<li>' . ($index) . '<ul>' . self::array2ul($item) . "</ul></li>";
            } else {
                $return .= '<li>';
                if (is_object($data)) {
                    $return .= ($index) . ' - ';
                }
                $return .= ($item) .'</li>';
            }
        }
        return $return;
    }
}
