``keys``
========

The ``keys`` filter returns the keys of a sequence or a mapping. It is useful
when you want to iterate over the keys of a sequence or a mapping:

.. code-block:: twig

    {% for key in ['a', 'b', 'c', 'd']|keys %}
        {{ key }}
    {% endfor %}
    {# outputs: 0 1 2 3 #}

    {% for key in {a: 'a_value', b: 'b_value'}|keys %}
        {{ key }}
    {% endfor %}
    {# outputs: a b #}

.. note::

    Internally, Twig uses the PHP `array_keys`_ function.

.. _`array_keys`: https://www.php.net/array_keys
