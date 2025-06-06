<?php
/**
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace SergeR\CakeUtility;

use Exception;
use InvalidArgumentException;

/**
 *
 */
class Hash
{
    /**
     * Apply a callback to a set of extracted values using `$function`.
     * The function will get the extracted values as the first argument.
     *
     * ### Example
     *
     * You can easily count the results of an extract using apply().
     * For example to count the comments on an Article:
     *
     * ```
     * $count = Hash::apply($data, 'Article.Comment.{n}', 'count');
     * ```
     *
     * You could also use a function like `array_sum` to sum the results.
     *
     * ```
     * $total = Hash::apply($data, '{n}.Item.price', 'array_sum');
     * ```
     *
     * @param array $data The data to reduce.
     * @param string $path The path to extract from $data.
     * @param callable $function The function to call on each extracted value.
     * @return mixed The results of the applied method.
     */
    public static function apply(array $data, string $path, callable $function): mixed
    {
        $values = static::extract($data, $path);
        return call_user_func($function, $values);
    }

    /**
     * Test whether or not a given path exists in $data.
     * This method uses the same path syntax as Hash::extract()
     *
     * Checking for paths that could target more than one element will
     * make sure that at least one matching element exists.
     *
     * @param array $data The data to check.
     * @param string $path The path to check for.
     * @return bool Existence of path.
     * @see Hash::extract()
     */
    public static function check(array $data, string $path): bool
    {
        $results = self::extract($data, $path);
        return count($results) > 0;
    }

    /**
     * Creates an associative array using `$keyPath` as the path to build its keys, and optionally
     * `$valuePath` as path to get the values. If `$valuePath` is not specified, all values will be initialized
     * to null (useful for Hash::merge). You can optionally group the values by what is obtained when
     * following the path specified in `$groupPath`.
     *
     * @param array $data Array from where to extract keys and values
     * @param string|array $keyPath A dot-separated string.
     * @param string|array|null $valuePath A dot-separated string.
     * @param string|null $groupPath A dot-separated string.
     * @return array Combined array
     * @throws Exception
     */
    public static function combine(array $data, string|array $keyPath, string|array|null $valuePath = null, ?string $groupPath = null): array
    {

        if (empty($data)) {
            return [];
        }

        if (is_array($keyPath)) {
            $format = array_shift($keyPath);
            $keys = static::format($data, $keyPath, $format);
        } else {
            $keys = static::extract($data, $keyPath);
        }
        if (empty($keys)) {
            return [];
        }

        if (!empty($valuePath) && is_array($valuePath)) {
            $format = array_shift($valuePath);
            $vals = static::format($data, $valuePath, $format);
        } elseif (!empty($valuePath)) {
            $vals = static::extract($data, $valuePath);
        }
        if (empty($vals)) {
            $vals = array_fill(0, count($keys), null);
        }

        if (count($keys) !== count($vals)) {
            throw new Exception('Hash::combine() needs an equal number of keys + values');
        }

        if ($groupPath !== null) {
            $group = static::extract($data, $groupPath);
            if (!empty($group)) {
                $c = count($keys);
                $out = [];
                for ($i = 0; $i < $c; $i++) {
                    if (!isset($group[$i])) {
                        $group[$i] = 0;
                    }
                    if (!isset($out[$group[$i]])) {
                        $out[$group[$i]] = [];
                    }
                    $out[$group[$i]][$keys[$i]] = $vals[$i];
                }
                return $out;
            }
        }
        if (empty($vals)) {
            return [];
        }
        return array_combine($keys, $vals);
    }

    /**
     * Determines if one array contains the exact keys and values of another.
     *
     * @param array $data The data to search through.
     * @param array $needle The values to file in $data
     * @return bool true if $data contains $needle, false otherwise
     */
    public static function contains(array $data, array $needle): bool
    {
        if (empty($data) || empty($needle)) {
            return false;
        }
        $stack = [];

        while (!empty($needle)) {
            $key = key($needle);
            $val = $needle[$key];
            unset($needle[$key]);

            if (array_key_exists($key, $data) && is_array($val)) {
                $next = $data[$key];
                unset($data[$key]);

                if (!empty($val)) {
                    $stack[] = [$val, $next];
                }
            } elseif (!array_key_exists($key, $data) || $data[$key] != $val) {
                return false;
            }

            if (empty($needle) && !empty($stack)) {
                [$needle, $data] = array_pop($stack);
            }
        }
        return true;
    }

    /**
     * Computes the difference between two complex arrays.
     * This method differs from the built-in array_diff() in that it will preserve keys
     * and work on multi-dimensional arrays.
     *
     * @param array $data First value
     * @param array $compare Second value
     * @return array Returns the key => value pairs that are not common in $data and $compare
     *    The expression for this function is ($data - $compare) + ($compare - ($data - $compare))
     */
    public static function diff(array $data, array $compare): array
    {
        if (empty($data)) {
            return $compare;
        }
        if (empty($compare)) {
            return $data;
        }
        $intersection = array_intersect_key($data, $compare);
        while (($key = key($intersection)) !== null) {
            if ($data[$key] == $compare[$key]) {
                unset($data[$key]);
                unset($compare[$key]);
            }
            next($intersection);
        }
        return $data + $compare;
    }

    /**
     * Counts the dimensions of an array.
     * Only considers the dimension of the first element in the array.
     *
     * If you have an un-even or heterogeneous array, consider using Hash::maxDimensions()
     * to get the dimensions of the array.
     *
     * @param array $data Array to count dimensions on
     * @return int The number of dimensions in $data
     */
    public static function dimensions(array $data): int
    {
        if (empty($data)) {
            return 0;
        }
        reset($data);
        $depth = 1;
        while (null !== ($elem = array_shift($data))) {
            if (is_array($elem)) {
                $depth += 1;
                $data =& $elem;
            } else {
                break;
            }
        }
        return $depth;
    }

    /**
     * Returns a formatted series of values extracted from `$data`, using
     * `$format` as the format and `$paths` as the values to extract.
     *
     * Usage:
     *
     * ```
     * $result = Hash::format($users, array('{n}.User.id', '{n}.User.name'), '%s : %s');
     * ```
     *
     * The `$format` string can use any format options that `vsprintf()` and `sprintf()` do.
     *
     * @param array $data Source array from which to extract the data
     * @param array $paths An array containing one or more Hash::extract()-style key paths
     * @param string $format Format string into which values will be inserted, see sprintf()
     * @return array|null An array of strings extracted from `$path` and formatted with `$format`
     */
    public static function format(array $data, array $paths, string $format): ?array
    {
        $extracted = [];
        $count = count($paths);

        if (!$count) {
            return null;
        }

        for ($i = 0; $i < $count; $i++) {
            $extracted[] = static::extract($data, $paths[$i]);
        }
        $out = [];
        $data = $extracted;
        $count = count($data[0]);

        $countTwo = count($data);
        for ($j = 0; $j < $count; $j++) {
            $args = [];
            for ($i = 0; $i < $countTwo; $i++) {
                if (array_key_exists($j, $data[$i])) {
                    $args[] = $data[$i][$j];
                }
            }
            $out[] = vsprintf($format, $args);
        }
        return $out;
    }

    /**
     * Get a single value specified by $path out of $data.
     * Does not support the full dot notation feature set,
     * but is faster for simple read operations.
     *
     * @param array $data Array of data to operate on.
     * @param mixed $path The path being searched for. Either a dot
     *   separated string, or an array of path segments.
     * @param mixed|null $default The return value when the path does not exist
     * @return mixed The value fetched from the array, or null.
     */
    public static function get(array $data, mixed $path, mixed $default = null): mixed
    {
        if (empty($data)) {
            return $default;
        }
        if (is_string($path) || is_numeric($path)) {
            $parts = explode('.', (string)$path);
        } elseif (is_bool($path) || $path === null) {
            $parts = [$path];
        } else {
            if (!is_array($path)) {
                throw new InvalidArgumentException(sprintf('Invalid Parameter %s, should be dot separated path or array', var_export($path, true)));
            }
            $parts = $path;
        }

        foreach ($parts as $key) {
            if (is_array($data) && isset($data[$key])) {
                $data =& $data[$key];
            } else {
                return $default;
            }
        }

        return $data;
    }

    /**
     * Expands a flat array to a nested array.
     *
     * For example, unflattens an array that was collapsed with `Hash::flatten()`
     * into a multi-dimensional array. So, `array('0.Foo.Bar' => 'Far')` becomes
     * `array(array('Foo' => array('Bar' => 'Far')))`.
     *
     * @param array $data Flattened array
     * @param string $separator The delimiter used
     * @return array
     */
    public static function expand(array $data, string $separator = '.'): array
    {
        $result = [];
        $stack = [];
        $separator = strlen($separator) ? $separator : '.';

        foreach ($data as $flat => $value) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $keys = explode($separator, $flat);
            $keys = array_reverse($keys);
            $child = [
                $keys[0] => $value
            ];
            array_shift($keys);
            foreach ($keys as $k) {
                $child = [
                    $k => $child
                ];
            }

            $stack[] = [$child, &$result];

            while (!empty($stack)) {
                foreach ($stack as $curKey => &$curMerge) {
                    foreach ($curMerge[0] as $key => &$val) {
                        if (!empty($curMerge[1][$key]) && (array)$curMerge[1][$key] === $curMerge[1][$key] && (array)$val === $val) {
                            $stack[] = [&$val, &$curMerge[1][$key]];
                        } elseif ((int)$key === $key && isset($curMerge[1][$key])) {
                            $curMerge[1][] = $val;
                        } else {
                            $curMerge[1][$key] = $val;
                        }
                    }
                    unset($stack[$curKey]);
                }
                unset($curMerge);
            }
        }
        return $result;
    }

    /**
     * Gets the values from an array matching the $path expression.
     * The path expression is a dot separated expression, that can contain a set
     * of patterns and expressions:
     *
     * - `{n}` Matches any numeric key, or integer.
     * - `{s}` Matches any string key.
     * - `Foo` Matches any key with the exact same value.
     *
     * There are a number of attribute operators:
     *
     *  - `=`, `!=` Equality.
     *  - `>`, `<`, `>=`, `<=` Value comparison.
     *  - `=/.../` Regular expression pattern match.
     *
     * Given a set of User array data, from a `$User->find('all')` call:
     *
     * - `1.User.name` Get the name of the user at index 1.
     * - `{n}.User.name` Get the name of every user in the set of users.
     * - `{n}.User[id]` Get the name of every user with an id key.
     * - `{n}.User[id>=2]` Get the name of every user with an id key greater than or equal to 2.
     * - `{n}.User[username=/^paul/]` Get User elements with username matching `^paul`.
     *
     * @param array $data The data to extract from.
     * @param string $path The path to extract.
     * @return array An array of the extracted values. Returns an empty array
     *   if there are no matches.
     */
    public static function extract(array $data, string $path): array
    {
        if (empty($path)) {
            return $data;
        }

        // Simple paths.
        if (!preg_match('/[{\[]/', $path)) {
            return (array)static::get($data, $path);
        }

        if (!str_contains($path, '[')) {
            $tokens = explode('.', $path);
        } else {
            $tokens = Text::tokenize($path, '.', '[', ']');
        }

        $_key = '__set_item__';

        $context = [$_key => [$data]];

        foreach ($tokens as $token) {
            $next = [];

            [$token, $conditions] = static::_splitConditions($token);

            foreach ($context[$_key] as $item) {
                foreach ((array)$item as $k => $v) {
                    if (static::_matchToken($k, $token)) {
                        $next[] = $v;
                    }
                }
            }

            // Filter for attributes.
            if ($conditions) {
                $filter = [];
                foreach ($next as $item) {
                    if (is_array($item) && static::_matches($item, $conditions)) {
                        $filter[] = $item;
                    }
                }
                $next = $filter;
            }
            $context = [$_key => $next];

        }
        return $context[$_key];
    }

    /**
     * Recursively filters a data set.
     *
     * @param array $data Either an array to filter, or value when in callback
     * @param array|callable $callback A function to filter the data with. Defaults to
     *   `static::_filter()` Which strips out all non-zero empty values.
     * @return array Filtered array
     */
    public static function filter(array $data, array|callable $callback = ['self', '_filter']): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = static::filter($v, $callback);
            }
        }
        return array_filter($data, $callback);
    }

    /**
     * Collapses a multi-dimensional array into a single dimension, using a delimited array path for
     * each array element's key, i.e. array(array('Foo' => array('Bar' => 'Far'))) becomes
     * array('0.Foo.Bar' => 'Far').)
     *
     * @param array $data Array to flatten
     * @param string $separator String used to separate array key elements in a path, defaults to '.'
     * @param string|null $path Initial path to prepend
     * @return array
     */
    public static function flatten(array $data, string $separator = '.', ?string $path = null): array
    {
        $result = [];
        $stack = [];
        while (!empty($data)) {
            $key = array_key_first($data);
            $element = $data[$key];
            unset($data[$key]);

            if (is_array($element) && !empty($element)) {
                if (!empty($data)) {
                    $stack[] = [$data, $path];
                }
                $data = $element;
                reset($data);

                $path = (string)$path . $key . $separator;
            } else {
                $result[(string)$path . $key] = $element;
            }

            if (empty($data) && !empty($stack)) {
                [$data, $path] = array_pop($stack);
                reset($data);
            }
        }
        return $result;
    }

    /**
     * Insert $values into an array with the given $path. You can use
     * `{n}` and `{s}` elements to insert $data multiple times.
     *
     * @param array $data The data to insert into.
     * @param string $path The path to insert at.
     * @param mixed|null $values The values to insert.
     * @return array The data with $values inserted.
     */
    public static function insert(array $data, string $path, mixed $values = null): array
    {
        if (!str_contains($path, '[')) {
            $tokens = explode('.', $path);
        } else {
            $tokens = Text::tokenize($path, '.', '[', ']');
        }

        if (!str_contains($path, '{') && !str_contains($path, '[')) {
            return static::_simpleOp('insert', $data, $tokens, $values);
        }

        $token = array_shift($tokens);
        $nextPath = implode('.', $tokens);

        [$token, $conditions] = static::_splitConditions($token);

        foreach ($data as $k => $v) {
            if (static::_matchToken($k, $token)) {
                if (!$conditions || static::_matches($v, $conditions)) {
                    $data[$k] = $nextPath
                        ? static::insert($v, $nextPath, $values)
                        : array_merge($v, (array)$values);
                }
            }
        }
        return $data;
    }

    /**
     * Map a callback across all elements in a set.
     * Can be provided a path to only modify slices of the set.
     *
     * @param array $data The data to map over, and extract data out of.
     * @param string $path The path to extract for mapping over.
     * @param callable $function The function to call on each extracted value.
     * @return array An array of the modified values.
     */
    public static function map(array $data, string $path, callable $function): array
    {
        $values = static::extract($data, $path);
        return array_map($function, $values);
    }

    /**
     * Counts the dimensions of *all* array elements. Useful for finding the maximum
     * number of dimensions in a mixed array.
     *
     * @param array $data Array to count dimensions on
     * @return int The maximum number of dimensions in $data
     */
    public static function maxDimensions(array $data): int
    {
        $depth = [];
        if (reset($data) !== false) {
            foreach ($data as $value) {
                $depth[] = is_array($value) ? static::maxDimensions($value) + 1 : 1;
            }
        }
        return empty($depth) ? 0 : max($depth);
    }

    /**
     * This function can be thought of as a hybrid between PHP's `array_merge` and `array_merge_recursive`.
     *
     * The difference between this method and the built-in ones, is that if an array key contains another array, then
     * Hash::merge() will behave in a recursive fashion (unlike `array_merge`). But it will not act recursively for
     * keys that contain scalar values (unlike `array_merge_recursive`).
     *
     * Note: This function will work with an unlimited amount of arguments and typecasts non-array parameters into
     * arrays.
     *
     * @param array $data Array to be merged
     * @param mixed $merge Array to merge with. The argument and all trailing arguments will be array cast when merged
     * @return array Merged array
     */
    public static function merge(array $data, mixed $merge): array
    {
        $args = array_slice(func_get_args(), 1);
        $return = $data;
        $stack = [];

        foreach ($args as &$curArg) {
            $stack[] = [(array)$curArg, &$return];
        }
        unset($curArg);

        while (!empty($stack)) {
            foreach ($stack as $curKey => &$curMerge) {
                foreach ($curMerge[0] as $key => &$val) {
                    if (!empty($curMerge[1][$key]) && (array)$curMerge[1][$key] === $curMerge[1][$key] && (array)$val === $val) {
                        $stack[] = [&$val, &$curMerge[1][$key]];
                    } elseif ((int)$key === $key && isset($curMerge[1][$key])) {
                        $curMerge[1][] = $val;
                    } else {
                        $curMerge[1][$key] = $val;
                    }
                }
                unset($stack[$curKey]);
            }
            unset($curMerge);
        }
        return $return;
    }

    /**
     * Merges the difference between $data and $compare onto $data.
     *
     * @param array $data The data to append onto.
     * @param array $compare The data to compare and append onto.
     * @return array The merged array.
     */
    public static function mergeDiff(array $data, array $compare): array
    {
        if (empty($data) && !empty($compare)) {
            return $compare;
        }
        if (empty($compare)) {
            return $data;
        }
        foreach ($compare as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            } elseif (is_array($value)) {
                $data[$key] = static::mergeDiff($data[$key], $value);
            }
        }
        return $data;
    }

    /**
     * Takes in a flat array and returns a nested array
     *
     * ### Options:
     *
     * - `children` The key name to use in the resultset for children.
     * - `idPath` The path to a key that identifies each entry. Should be
     *   compatible with Hash::extract(). Defaults to `{n}.$alias.id`
     * - `parentPath` The path to a key that identifies the parent of each entry.
     *   Should be compatible with Hash::extract(). Defaults to `{n}.$alias.parent_id`
     * - `root` The id of the desired top-most result.
     *
     * @param array $data The data to nest.
     * @param array $options Options are:
     * @return array of results, nested
     * @throws InvalidArgumentException When providing invalid data.
     */
    public static function nest(array $data, array $options = []): array
    {
        if (!$data) {
            return $data;
        }

        $alias = key(current($data));
        $options += [
            'idPath'     => "{n}.$alias.id",
            'parentPath' => "{n}.$alias.parent_id",
            'children'   => 'children',
            'root'       => null
        ];

        $return = $idMap = [];
        $ids = static::extract($data, $options['idPath']);

        $idKeys = explode('.', $options['idPath']);
        array_shift($idKeys);

        $parentKeys = explode('.', $options['parentPath']);
        array_shift($parentKeys);

        foreach ($data as $result) {
            $result[$options['children']] = [];

            $id = static::get($result, $idKeys);
            $parentId = static::get($result, $parentKeys);

            if (isset($idMap[$id][$options['children']])) {
                $idMap[$id] = array_merge($result, $idMap[$id]);
            } else {
                $idMap[$id] = array_merge($result, [$options['children'] => []]);
            }
            if (!$parentId || !in_array($parentId, $ids)) {
                $return[] =& $idMap[$id];
            } else {
                $idMap[$parentId][$options['children']][] =& $idMap[$id];
            }
        }

        if (!$return) {
            throw new InvalidArgumentException('Invalid data array to nest');
        }

        if ($options['root']) {
            $root = $options['root'];
        } else {
            $root = static::get($return[0], $parentKeys);
        }

        foreach ($return as $i => $result) {
            $id = static::get($result, $idKeys);
            $parentId = static::get($result, $parentKeys);
            if ($id !== $root && $parentId != $root) {
                unset($return[$i]);
            }
        }
        return array_values($return);
    }

    /**
     * Normalizes an array, and converts it to a standard format.
     *
     * @param array $data List to normalize
     * @param bool $assoc If true, $data will be converted to an associative array.
     * @return array
     */
    public static function normalize(array $data, bool $assoc = true): array
    {
        $keys = array_keys($data);
        $count = count($keys);
        $numeric = true;

        if (!$assoc) {
            for ($i = 0; $i < $count; $i++) {
                if (!is_int($keys[$i])) {
                    $numeric = false;
                    break;
                }
            }
        }
        if (!$numeric || $assoc) {
            $newList = [];
            for ($i = 0; $i < $count; $i++) {
                if (is_int($keys[$i])) {
                    $newList[$data[$keys[$i]]] = null;
                } else {
                    $newList[$keys[$i]] = $data[$keys[$i]];
                }
            }
            $data = $newList;
        }
        return $data;
    }

    /**
     * Checks to see if all the values in the array are numeric
     *
     * @param array $data The array to check.
     * @return bool true if values are numeric, false otherwise
     */
    public static function numeric(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        return $data === array_filter($data, 'is_numeric');
    }

    /**
     * Reduce a set of extracted values using `$function`.
     *
     * @param array $data The data to reduce.
     * @param string $path The path to extract from $data.
     * @param callable $function The function to call on each extracted value.
     * @return mixed The reduced value.
     */
    public static function reduce(array $data, string $path, callable $function): mixed
    {
        $values = static::extract($data, $path);
        return array_reduce($values, $function);
    }

    /**
     * Remove data matching $path from the $data array.
     * You can use `{n}` and `{s}` to remove multiple elements
     * from $data.
     *
     * @param array $data The data to operate on
     * @param string $path A path expression to use to remove.
     * @return array The modified array.
     */
    public static function remove(array $data, string $path): array
    {
        if (!str_contains($path, '[')) {
            $tokens = explode('.', $path);
        } else {
            $tokens = Text::tokenize($path, '.', '[', ']');
        }

        if (!str_contains($path, '{') && !str_contains($path, '[')) {
            return static::_simpleOp('remove', $data, $tokens);
        }

        $token = array_shift($tokens);
        $nextPath = implode('.', $tokens);

        [$token, $conditions] = static::_splitConditions($token);

        foreach ($data as $k => $v) {
            $match = static::_matchToken($k, $token);
            if ($match && is_array($v)) {
                if ($conditions) {
                    if (static::_matches($v, $conditions)) {
                        if ($nextPath !== '') {
                            $data[$k] = static::remove($v, $nextPath);
                        } else {
                            unset($data[$k]);
                        }
                    }
                } else {
                    $data[$k] = static::remove($v, $nextPath);
                }
                if (empty($data[$k])) {
                    unset($data[$k]);
                }
            } elseif ($match && $nextPath === '') {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * Sorts an array by any value, determined by a Hash-compatible path
     *
     * ### Sort directions
     *
     * - `asc` Sort ascending.
     * - `desc` Sort descending.
     *
     * ### Sort types
     *
     * - `regular` For regular sorting (don't change types)
     * - `numeric` Compare values numerically
     * - `string` Compare values as strings
     * - `locale` Compare items as strings, based on the current locale
     * - `natural` Compare items as strings using "natural ordering" in a human friendly way.
     *   Will sort foo10 below foo2 as an example. Requires PHP 5.4 or greater or it will fallback to 'regular'
     *
     * To do case insensitive sorting, pass the type as an array as follows:
     *
     * ```
     * array('type' => 'regular', 'ignoreCase' => true)
     * ```
     *
     * When using the array form, `type` defaults to 'regular'. The `ignoreCase` option
     * defaults to `false`.
     *
     * @param array $data An array of data to sort
     * @param string $path A Hash-compatible path to the array value
     * @param string $dir See directions above. Defaults to 'asc'.
     * @param array|string $type See direction types above. Defaults to 'regular'.
     * @return array Sorted array of data
     */
    public static function sort(array $data, string $path, string $dir = 'asc', array|string $type = 'regular'): array
    {
        if (empty($data)) {
            return [];
        }
        $originalKeys = array_keys($data);
        $numeric = is_numeric(implode('', $originalKeys));
        if ($numeric) {
            $data = array_values($data);
        }
        $sortValues = static::extract($data, $path);
        $dataCount = count($data);

        // Make sortValues match the data length, as some keys could be missing
        // the sorted value path.
        $missingData = count($sortValues) < $dataCount;
        if ($missingData && $numeric) {
            // Get the path without the leading '{n}.'
            $itemPath = substr($path, 4);
            foreach ($data as $key => $value) {
                $sortValues[$key] = static::get($value, $itemPath);
            }
        } elseif ($missingData) {
            $sortValues = array_pad($sortValues, $dataCount, null);
        }
        $result = static::_squash($sortValues);
        $keys = static::extract($result, '{n}.id');
        $values = static::extract($result, '{n}.value');

        $dir = strtolower($dir);
        $ignoreCase = false;

        // $type can be overloaded for case insensitive sort
        if (is_array($type)) {
            $type += ['ignoreCase' => false, 'type' => 'regular'];
            $ignoreCase = $type['ignoreCase'];
            $type = $type['type'];
        }
        $type = strtolower($type);

        if ($dir === 'asc') {
            $dir = SORT_ASC;
        } else {
            $dir = SORT_DESC;
        }
        if ($type === 'numeric') {
            $type = SORT_NUMERIC;
        } elseif ($type === 'string') {
            $type = SORT_STRING;
        } elseif ($type === 'natural') {
            $type = SORT_NATURAL;
        } elseif ($type === 'locale') {
            $type = SORT_LOCALE_STRING;
        } else {
            $type = SORT_REGULAR;
        }

        if ($ignoreCase) {
            $values = array_map('mb_strtolower', $values);
        }
        array_multisort($values, $dir, $type, $keys, $dir);

        $sorted = [];
        $keys = array_unique($keys);

        foreach ($keys as $k) {
            if ($numeric) {
                $sorted[] = $data[$k];
                continue;
            }
            if (isset($originalKeys[$k])) {
                $sorted[$originalKeys[$k]] = $data[$originalKeys[$k]];
            } else {
                $sorted[$k] = $data[$k];
            }
        }
        return $sorted;
    }

    /**
     * Callback function for filtering.
     *
     * @param mixed $var Array to filter.
     * @return bool
     */
    protected static function _filter(mixed $var): bool
    {
        if ($var === 0 || $var === 0.0 || $var === '0' || !empty($var)) {
            return true;
        }
        return false;
    }

    /**
     * Checks whether or not $data matches the attribute patterns
     *
     * @param array $data Array of data to match.
     * @param string $selector The patterns to match.
     * @return bool Fitness of expression.
     * @noinspection RegExpUnnecessaryNonCapturingGroup,RegExpRedundantEscape
     */
    protected static function _matches(array $data, string $selector): bool
    {
        preg_match_all(
            '/(\[ (?P<attr>[^=><!]+?) (\s* (?P<op>[><!]?[=]|[><]) \s* (?P<val>(?:\/.*?\/ | [^\]]+)) )? \])/x',
            $selector,
            $conditions,
            PREG_SET_ORDER
        );

        foreach ($conditions as $cond) {
            $attr = $cond['attr'];
            $op = $cond['op'] ?? null;
            $val = $cond['val'] ?? null;

            // Presence test.
            if (empty($op) && empty($val) && !isset($data[$attr])) {
                return false;
            }

            // Empty attribute = fail.
            if (!(isset($data[$attr]) || array_key_exists($attr, $data))) {
                return false;
            }

            $prop = null;
            if (isset($data[$attr])) {
                $prop = $data[$attr];
            }
            $isBool = is_bool($prop);
            if ($isBool && is_numeric($val)) {
                $prop = $prop ? '1' : '0';
            } elseif ($isBool) {
                $prop = $prop ? 'true' : 'false';
            }

            // Pattern matches and other operators.
            if ($op === '=' && $val && $val[0] === '/' && is_string($prop)) {
                if (!preg_match($val, $prop)) {
                    return false;
                }
            } elseif (($op === '=' && $prop != $val) ||
                ($op === '!=' && $prop == $val) ||
                ($op === '>' && $prop <= $val) ||
                ($op === '<' && $prop >= $val) ||
                ($op === '>=' && $prop < $val) ||
                ($op === '<=' && $prop > $val)
            ) {
                return false;
            }

        }
        return true;
    }

    /**
     * Check a key against a token.
     *
     * @param mixed $key The key in the array being searched.
     * @param string $token The token being matched.
     * @return bool
     */
    protected static function _matchToken(mixed $key, string $token): bool
    {
        return match ($token) {
            '{n}' => is_numeric($key),
            '{s}' => is_string($key),
            '{*}' => true,
            default => is_numeric($token) ? ($key == $token) : $key === $token,
        };
    }

    /**
     * Perform a simple insert/remove operation.
     *
     * @param string $op The operation to do.
     * @param array $data The data to operate on.
     * @param array $path The path to work on.
     * @param mixed|null $values The values to insert when doing inserts.
     * @return array data.
     */
    protected static function _simpleOp(string $op, array $data, array $path, mixed $values = null): array
    {
        $_list =& $data;

        $count = count($path);
        $last = $count - 1;
        foreach ($path as $i => $key) {
            if ($op === 'insert') {
                if ($i === $last) {
                    $_list[$key] = $values;
                    return $data;
                }
                if (!isset($_list[$key])) {
                    $_list[$key] = [];
                }
                $_list =& $_list[$key];
                if (!is_array($_list)) {
                    $_list = [];
                }
            } elseif ($op === 'remove') {
                if ($i === $last) {
                    unset($_list[$key]);
                    return $data;
                }
                if (!isset($_list[$key])) {
                    return $data;
                }
                $_list =& $_list[$key];
            }
        }

        return $data;
    }

    /**
     * Split token conditions
     *
     * @param string $token the token being splitted.
     * @return array array(token, conditions) with token splitted
     */
    protected static function _splitConditions($token)
    {
        $conditions = false;
        $position = strpos($token, '[');
        if ($position !== false) {
            $conditions = substr($token, $position);
            $token = substr($token, 0, $position);
        }

        return [$token, $conditions];
    }

    /**
     * Helper method for sort()
     * Squashes an array to a single hash so it can be sorted.
     *
     * @param array $data The data to squash.
     * @param string|null $key The key for the data.
     * @return array
     */
    protected static function _squash(array $data, ?string $key = null): array
    {
        $stack = [];
        foreach ($data as $k => $r) {
            $id = $k;
            if ($key !== null) {
                $id = $key;
            }
            if (is_array($r) && !empty($r)) {
                $stack = array_merge($stack, static::_squash($r, $id));
            } else {
                $stack[] = ['id' => $id, 'value' => $r];
            }
        }
        return $stack;
    }
}
